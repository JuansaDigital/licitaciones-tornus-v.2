<?php

namespace App\Services;

use App\Models\Licitacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAffinityService
{
    protected ?string $apiKey;
    protected string $model;
    protected array $agencyProfile = [];

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->model = env('GEMINI_MODEL', 'gemini-2.5-flash');
        $this->loadAgencyProfile();
    }

    protected function loadAgencyProfile(): void
    {
        $profilePath = storage_path('app/tornus_profile.json');
        if (!file_exists($profilePath)) {
            $profilePath = base_path('informacion extra/LICITACIONES TORNUS COLOMBIA/data/tornus_profile.json');
        }

        if (file_exists($profilePath)) {
            $this->agencyProfile = json_decode(file_get_contents($profilePath), true) ?: [];
        }
    }

    /**
     * Evalúa una licitación contra el perfil de Tornus Agency
     */
    public function evaluate(Licitacion $licitacion): array
    {
        $profileJson = json_encode($this->agencyProfile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<EOT
Eres el director de licitaciones y desarrollo de negocios de la agencia Tornus.
Tu tarea es analizar la siguiente oportunidad de contratación pública y evaluar su afinidad estratégica con el perfil y capacidades de Tornus.

PERFIL DE TORNUS AGENCY:
{$profileJson}

DATOS DE LA LICITACIÓN:
- Identificador: {$licitacion->referencia}
- Entidad Convocante: {$licitacion->entidad}
- Departamento / Región: {$licitacion->departamento}
- Objeto del Contrato: {$licitacion->objeto}
- Cuantía / Presupuesto: {$licitacion->formatted_cuantia} {$licitacion->moneda}
- Modalidad: {$licitacion->tipo_proceso}

INSTRUCCIONES DE RESPUESTA:
Debes responder ESTRICTAMENTE en formato JSON válido, sin bloques de código markdown ni texto adicional.
El JSON debe tener la siguiente estructura exacta:
{
  "score": (entero entre 0 y 100),
  "nivel": ("ALTA" si score >= 70, "MEDIA" si score entre 40 y 69, "BAJA" si score < 40),
  "justificacion": "Texto explicativo ejecutivo de máximo 2 párrafos sobre por qué encaja o no con Tornus.",
  "servicios_sugeridos": ["Servicio 1", "Servicio 2"],
  "estrategia_postulacion": "Postulación Directa" o "Consorcio / UT" o "Descartar",
  "puntos_clave": "Puntos críticos del pliego a tener en cuenta."
}
EOT;

        // Si hay API key disponible, consultar la API de Gemini
        if (!empty($this->apiKey)) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

                $response = Http::timeout(25)->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanJson = trim($text);
                    if (str_starts_with($cleanJson, '```json')) {
                        $cleanJson = substr($cleanJson, 7);
                    }
                    if (str_ends_with($cleanJson, '```')) {
                        $cleanJson = substr($cleanJson, 0, -3);
                    }
                    $result = json_decode(trim($cleanJson), true);

                    if (is_array($result) && isset($result['score'])) {
                        $this->saveEvaluation($licitacion, $result);
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Gemini API call failed, falling back to heuristic engine: " . $e->getMessage());
            }
        }

        // Fallback Heurístico determinista para asegurar disponibilidad
        $fallback = $this->heuristicEvaluation($licitacion);
        $this->saveEvaluation($licitacion, $fallback);
        return $fallback;
    }

    /**
     * Motor heurístico determinista de afinidad
     */
    protected function heuristicEvaluation(Licitacion $licitacion): array
    {
        $text = mb_strtoupper($licitacion->objeto . ' ' . $licitacion->entidad, 'UTF-8');
        
        $keywordsHigh = ['TURISMO', 'DESTINO', 'BRANDING', 'PROMOCIÓN TURÍSTICA', 'AUDIOVISUAL', 'PAUTA', 'MARKETING DIGITAL'];
        $keywordsMedium = ['COMUNICACIONES', 'EVENTOS', 'PUBLICIDAD', 'PÁGINA WEB', 'FOTOGRAFÍA', 'DISEÑO'];
        $keywordsNegative = ['OBRA', 'VIGILANCIA', 'ALIMENTACIÓN', 'PAVIMENTACIÓN', 'ASEO', 'VEHÍCULOS'];

        $score = 25;
        $servicios = [];

        foreach ($keywordsHigh as $kw) {
            if (str_contains($text, $kw)) {
                $score += 20;
                $servicios[] = ucfirst(strtolower($kw));
            }
        }

        foreach ($keywordsMedium as $kw) {
            if (str_contains($text, $kw)) {
                $score += 10;
                $servicios[] = ucfirst(strtolower($kw));
            }
        }

        foreach ($keywordsNegative as $kw) {
            if (str_contains($text, $kw)) {
                $score -= 30;
            }
        }

        $score = max(5, min(95, $score));

        $nivel = 'BAJA';
        $estrategia = 'Descartar';
        if ($score >= 70) {
            $nivel = 'ALTA';
            $estrategia = $licitacion->cuantia > 500000000 ? 'Consorcio / UT' : 'Postulación Directa';
        } elseif ($score >= 40) {
            $nivel = 'MEDIA';
            $estrategia = 'Consorcio / UT';
        }

        return [
            'score' => $score,
            'nivel' => $nivel,
            'justificacion' => "Análisis de afinidad basado en el objeto contractual. Presenta coincidencia en " . count($servicios) . " áreas de competencia directa de Tornus.",
            'servicios_sugeridos' => array_values(array_unique($servicios)),
            'estrategia_postulacion' => $estrategia,
            'puntos_clave' => "Verificar términos de referencia y habilitantes jurídicos antes de postulación."
        ];
    }

    protected function saveEvaluation(Licitacion $licitacion, array $data): void
    {
        $licitacion->score = $data['score'] ?? null;
        $licitacion->nivel = $data['nivel'] ?? null;
        $licitacion->justificacion = $data['justificacion'] ?? null;
        $licitacion->servicios_sugeridos = $data['servicios_sugeridos'] ?? [];
        $licitacion->estrategia_postulacion = $data['estrategia_postulacion'] ?? null;
        $licitacion->puntos_clave = $data['puntos_clave'] ?? null;
        $licitacion->evaluado_en = now();
        $licitacion->save();
    }
}
