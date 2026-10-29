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
        $this->agencyProfile = \App\Http\Controllers\PerfilTornusController::getActiveProfile();
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

                $response = Http::withoutVerifying()->timeout(25)->post($endpoint, [
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
     * Motor heurístico determinista de afinidad contrastado con el perfil de Tornus
     */
    protected function heuristicEvaluation(Licitacion $licitacion): array
    {
        $text = mb_strtoupper($licitacion->objeto . ' ' . $licitacion->entidad . ' ' . $licitacion->categoria, 'UTF-8');
        
        $exclusions = $this->agencyProfile['criterios_exclusion'] ?? [
            'Obras civiles', 'Infraestructura vial', 'Pavimentación', 'Vigilancia', 'Alimentación', 'Aseo', 'Vehículos', 'Dotaciones'
        ];

        // 1. Verificar si coincide con criterios de exclusión
        foreach ($exclusions as $ex) {
            $words = preg_split('/[\s,]+/', mb_strtoupper($ex, 'UTF-8'));
            foreach ($words as $w) {
                if (strlen($w) > 4 && str_contains($text, $w)) {
                    return [
                        'score' => 0,
                        'nivel' => 'BAJA',
                        'aplica' => 'NO APLICA',
                        'justificacion' => "El proceso corresponde a actividades de {$w} o afines, lo cual encaja de manera explícita en los criterios de exclusión de Tornus Agency. No existe ningún componente prioritario de mercadeo, turismo, comunicaciones ni posicionamiento territorial. Por ende, no es viable ni estratégico participar en esta convocatoria.",
                        'servicios_sugeridos' => [],
                        'estrategia_postulacion' => 'Descartar',
                        'puntos_clave' => "El objeto contractual es completamente ajeno al core de negocio de la agencia (servicios operativos o técnicos frente a marketing turístico)."
                    ];
                }
            }
        }

        // 2. Verificar afinidad positiva con servicios y entidades objetivo
        $keywordsHigh = ['TURISMO', 'DESTINO', 'BRANDING', 'PROMOCIÓN', 'AUDIOVISUAL', 'PAUTA', 'MARKETING DIGITAL', 'FAMTRIP', 'PORTAL WEB', 'CAMPAÑA'];
        $keywordsMedium = ['COMUNICACIONES', 'EVENTOS', 'PUBLICIDAD', 'PÁGINA WEB', 'FOTOGRAFÍA', 'DISEÑO', 'CONTENIDO'];

        $score = 30;
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

        $score = max(5, min(95, $score));

        $nivel = 'BAJA';
        $aplica = 'NO APLICA';
        $estrategia = 'Descartar';

        if ($score >= 70) {
            $nivel = 'ALTA';
            $aplica = 'APLICA';
            $estrategia = $licitacion->cuantia > 800000000 ? 'Consorcio / UT' : 'Postulación Directa';
            $justificacion = "El proceso se alinea directamente con la propuesta de valor y los servicios principales de Tornus Agency en promoción y posicionamiento. Existe una alta oportunidad para desplegar estrategias creativas y de comunicación turística.";
            $puntosClave = "Revisar los requisitos habilitantes jurídicos y financieros, y verificar si se requiere consorcio según el monto oficial.";
        } elseif ($score >= 40) {
            $nivel = 'MEDIA';
            $aplica = 'APLICA CON CONDICIONES';
            $estrategia = 'Consorcio / UT';
            $justificacion = "El proceso contiene componentes que coinciden parcialmente con las capacidades de Tornus (" . implode(', ', $servicios) . "). Requiere evaluar si las actividades secundarias ameritan participar en alianza con un socio local.";
            $puntosClave = "Verificar pliegos definitivos y evaluar si se postula en consorcio para cubrir el alcance complementario.";
        } else {
            $justificacion = "El proceso presenta una baja coincidencia con el core de Tornus Agency. Las actividades descritas no están centradas en mercadeo, comunicaciones ni turismo estratégico.";
            $puntosClave = "No se recomienda destinar recursos de formulación a menos que exista un socio líder interesado en consorcio.";
        }

        return [
            'score' => $score,
            'nivel' => $nivel,
            'aplica' => $aplica,
            'justificacion' => $justificacion,
            'servicios_sugeridos' => array_values(array_unique($servicios)),
            'estrategia_postulacion' => $estrategia,
            'puntos_clave' => $puntosClave
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
