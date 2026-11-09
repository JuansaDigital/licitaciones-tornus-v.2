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
        $this->model = env('GEMINI_MODEL', 'gemini-2.0-flash');
        $this->loadAgencyProfile();
    }

    protected function loadAgencyProfile(): void
    {
        $this->agencyProfile = \App\Http\Controllers\PerfilTornusController::getActiveProfile();
    }

    /**
     * Evalúa una licitación contra el modelo de 3 pasos de Tornus S.A.S.
     */
    public function evaluate(Licitacion $licitacion): array
    {
        $objetoUpper = mb_strtoupper(trim((string)$licitacion->objeto), 'UTF-8');
        $modalidadUpper = mb_strtoupper(trim((string)$licitacion->tipo_proceso), 'UTF-8');
        $cuantia = (float)($licitacion->cuantia ?? 0);

        // Pre-filtro F1: Contratación directa de persona natural / apoyo a la gestión
        $isContratacionDirecta = str_contains($modalidadUpper, 'DIRECTA');
        $isApoyoGestion = str_contains($objetoUpper, 'APOYO A LA GESTIÓN') 
            || str_contains($objetoUpper, 'APOYO A LA GESTION')
            || str_contains($objetoUpper, 'PRESTACIÓN DE SERVICIOS PROFESIONALES DE APOYO')
            || str_contains($objetoUpper, 'PRESTACION DE SERVICIOS PROFESIONALES DE APOYO')
            || str_contains($objetoUpper, 'HONORARIOS');

        if ($isContratacionDirecta && $isApoyoGestion) {
            $discard = [
                'filtro_descarte' => 'F1',
                'puntajes' => [
                    'encaje_linea' => 0,
                    'experiencia' => 0,
                    'entidad' => 0,
                    'escala' => 0,
                    'territorio_tiempo' => 0
                ],
                'score' => 0,
                'nivel' => 'D',
                'etiqueta' => 'DESCARTAR',
                'lineas_tornus' => [],
                'antecedente_tornus' => null,
                'componente_aliado' => null,
                'categoria' => 'Otro',
                'dictamen' => 'Proceso de contratación directa dirigido a persona natural para apoyo a la gestión. Incompatible con la figura corporativa de Tornus S.A.S.',
                'punto_clave' => 'Filtro F1: Contratación de persona natural.',
                'estrategia_postulacion' => 'Descartar'
            ];
            $this->saveEvaluation($licitacion, $discard);
            return $discard;
        }

        // Pre-filtro F3: Cuantía conocida menor a $100M COP
        if ($cuantia > 0 && $cuantia < 100000000) {
            $discard = [
                'filtro_descarte' => 'F3',
                'puntajes' => [
                    'encaje_linea' => 0,
                    'experiencia' => 0,
                    'entidad' => 0,
                    'escala' => 0,
                    'territorio_tiempo' => 0
                ],
                'score' => 0,
                'nivel' => 'D',
                'etiqueta' => 'DESCARTAR',
                'lineas_tornus' => [],
                'antecedente_tornus' => null,
                'componente_aliado' => null,
                'categoria' => 'Otro',
                'dictamen' => 'Cuantía presupuestada de ' . $licitacion->formatted_cuantia . ' inferior al umbral mínimo corporativo de $100M COP establecido por Tornus.',
                'punto_clave' => 'Filtro F3: Cuantía menor al mínimo de $100M COP.',
                'estrategia_postulacion' => 'Descartar'
            ];
            $this->saveEvaluation($licitacion, $discard);
            return $discard;
        }

        // Prompt Oficial Estructurado de 3 Pasos
        $prompt = <<<EOT
ROL:
Eres el analista de nuevos negocios de Tornus S.A.S., agencia colombiana de placebranding y marketing de destinos. Evalúas procesos de contratación pública de SECOP II para decidir si Tornus debe presentarse. Eres estricto: es preferible descartar un proceso dudoso que hacer perder tiempo al equipo comercial. Nunca asignes un puntaje por defecto.

PERFIL DE TORNUS:
Líneas de servicio:
1. Branding y marca de destino o territorio
2. Inteligencia de mercados y estrategia de datos turísticos
3. Planeación estratégica de turismo y marketing territorial
4. Desarrollo creativo y producción de contenido
5. Activación táctica, campañas, plan de medios y comunicaciones
6. Fortalecimiento de capacidades (formación a prestadores, empresarios y entidades)

Contratos acreditables:
- Promoción y posicionamiento turístico de La Guajira (Descubre La Guajira) - LP-006-2025 Gobernación de La Guajira ($6.355M COP)
- Promoción turística del Valle del Cauca (Visit Valle del Cauca) - Cali-Valle Bureau ($2.380M COP)
- Fortalecimiento de la competitividad y estrategia turística del Magdalena - Cámara de Comercio de Santa Marta ($2.170M COP)
- Campaña y posicionamiento de destino Armenia y ruedas de negocios - Alcaldía de Armenia ($1.450M COP)
- Estrategia y producción de contenidos Colombia El País de la Belleza

Mercados prioritarios: región Caribe, Bogotá, Medellín, Cali y los principales destinos turísticos de Colombia.
Rango de cuantía óptimo: $200M a $3.000M COP. Cuantía mínima: $100M COP.

DATOS DEL PROCESO A EVALUAR:
Entidad: {$licitacion->entidad}
Objeto: {$licitacion->objeto}
Modalidad: {$licitacion->tipo_proceso}
Tipo de contrato: {$licitacion->tipo_contrato}
Cuantía: {$licitacion->formatted_cuantia} {$licitacion->moneda}
Departamento / municipio: {$licitacion->departamento} / {$licitacion->municipio}
Fecha de cierre: {$licitacion->fecha_cierre}
Etapa: {$licitacion->fase} {$licitacion->estado}

PROCEDIMIENTO DE EVALUACIÓN (Síguelo estrictamente en orden):
Paso 1. Filtros de descarte. Si se cumple cualquiera, score = 0, nivel = D, y no evalúes el resto:
- F1: contratación directa de prestación de servicios profesionales o de apoyo a la gestión dirigida a persona natural.
- F2: el objeto no incluye marca, promoción, mercadeo, comunicación, medios, contenido, estrategia, inteligencia de mercados ni formación. La palabra "turismo" en el nombre de la entidad, de un edificio o de un proyecto NO cuenta como encaje.
- F3: cuantía conocida menor a $100M COP.

Paso 2. Rúbrica (suma de 0 a 100). Evalúa solo lo que dice el objeto y los documentos, no la entidad:
- Encaje con líneas de servicio (0-35): 35 promoción/posicionamiento de destino, branding territorial, estrategia de marketing turístico o inteligencia turística; 25 plan de medios con estrategia, campañas, contenido o formación a prestadores/empresarios; 15 famtrips, ferias, activaciones o eventos promocionales; 0-5 logística o suministro puro.
- Experiencia acreditable (0-25): 25 contratos similares en objeto y cuantía; 15 experiencia parcial o de menor cuantía; 5 solo con aliado; 0 sin experiencia relacionada.
- Tipo de entidad (0-15): 15 entidad de turismo, FONTUR, gobernación o alcaldía con objeto turístico; 8 entidad pública con objeto de comunicación institucional; 3 otra.
- Escala y capacidad (0-15): 15 dentro del rango óptimo ($200M a $3.000M); 8 por encima (requiere consorcio) o entre el mínimo y el rango óptimo; 3 cerca del mínimo.
- Territorio y tiempo (0-10): +5 mercado prioritario; +5 más de 10 días hábiles para ofertar o etapa de RFI.

Paso 3. Nivel:
- A (75-100): APLICA DIRECTO
- B (55-74): APLICA CON ALIADO
- C (35-54): MONITOREAR
- D (0-34): DESCARTAR
Regla de corte: si Tornus no cumple la experiencia habilitante ni con un aliado, el nivel máximo es C.
Si la información no alcanza para evaluar, devuelve nivel "PENDIENTE" y score null.

Paso 4. Dictamen:
Debe ser coherente con el nivel: un nivel A o B nunca dice "no destinar esfuerzos" y un nivel D nunca dice "aplica". Menciona por nombre el contrato de Tornus que sirve de antecedente, si existe. Si el nivel es B, di qué componente debe cubrir el aliado.

EJEMPLOS DE CALIBRACIÓN:
- "Estrategia integral de promoción turística nacional y marketing de destinos", FONTUR, $850M -> A (líneas 1-3, entidad de turismo, cuantía en rango).
- "Plan de medios y generación de contenidos institucionales", Municipio de Cúcuta, $1.440M -> B (línea 5, entidad con comunicación institucional, requiere validar experiencia en compra de medios).
- "Fortalecimiento de capacidades turísticas de mipymes con formación y suministro de insumos", FDL La Candelaria -> B (línea 6, aliado logístico para el suministro).
- "Logística de famtrip en Bahía Málaga", FONTUR -> C (activación, pero el objeto es logístico).
- "Revisión energética del Centro para la Biodiversidad y el Turismo", SENA -> D por F2.
- "Apoyo a la gestión en la Secretaría de Turismo", contratación directa, $8M -> D por F1.

FORMATO DE RESPUESTA (ESTRICTAMENTE JSON VÁLIDO, SIN BLOQUES MARKDOWN NI TEXTO ADICIONAL):
{
  "filtro_descarte": null,
  "puntajes": {
    "encaje_linea": 0,
    "experiencia": 0,
    "entidad": 0,
    "escala": 0,
    "territorio_tiempo": 0
  },
  "score": 0,
  "nivel": "A",
  "etiqueta": "APLICA DIRECTO",
  "lineas_tornus": [1, 3],
  "antecedente_tornus": "Nombre del contrato o null",
  "componente_aliado": "Componente a cubrir por aliado o null",
  "categoria": "Promoción de destino",
  "dictamen": "Máximo 3 frases coherentes con el nivel.",
  "punto_clave": "La única cosa crítica que el equipo comercial debe verificar antes de decidir."
}
EOT;

        if (!empty($this->apiKey)) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

                $response = Http::withoutVerifying()->timeout(30)->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $json = $response->json();

                    // Registro de consumo de tokens
                    $usage = $json['usageMetadata'] ?? [];
                    $promptTokens = (int)($usage['promptTokenCount'] ?? 0);
                    $candidateTokens = (int)($usage['candidatesTokenCount'] ?? 0);
                    if ($promptTokens > 0 || $candidateTokens > 0) {
                        try {
                            \App\Models\AiTokenUsage::recordUsage(
                                model: $this->model,
                                promptTokens: $promptTokens,
                                candidateTokens: $candidateTokens,
                                licitacionId: $licitacion->id,
                                market: MarketService::current()
                            );
                        } catch (\Throwable $e) {
                            Log::warning("AiTokenUsage record error: " . $e->getMessage());
                        }
                    }

                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanJson = trim($text);
                    if (str_starts_with($cleanJson, '```json')) {
                        $cleanJson = substr($cleanJson, 7);
                    }
                    if (str_ends_with($cleanJson, '```')) {
                        $cleanJson = substr($cleanJson, 0, -3);
                    }
                    $result = json_decode(trim($cleanJson), true);

                    if (is_array($result) && (isset($result['score']) || isset($result['nivel']))) {
                        // Consistencia de subpuntajes
                        if (isset($result['puntajes']) && is_array($result['puntajes'])) {
                            $calcScore = array_sum($result['puntajes']);
                            if ($result['score'] === null && $result['nivel'] !== 'PENDIENTE') {
                                $result['score'] = $calcScore;
                            }
                        }

                        // Consistencia de nivel y etiqueta
                        $score = $result['score'];
                        if ($result['nivel'] === 'PENDIENTE' || $score === null) {
                            $result['nivel'] = 'PENDIENTE';
                            $result['etiqueta'] = 'PENDIENTE';
                        } elseif ($score >= 75) {
                            $result['nivel'] = 'A';
                            $result['etiqueta'] = 'APLICA DIRECTO';
                        } elseif ($score >= 55) {
                            $result['nivel'] = 'B';
                            $result['etiqueta'] = 'APLICA CON ALIADO';
                        } elseif ($score >= 35) {
                            $result['nivel'] = 'C';
                            $result['etiqueta'] = 'MONITOREAR';
                        } else {
                            $result['nivel'] = 'D';
                            $result['etiqueta'] = 'DESCARTAR';
                        }

                        $this->saveEvaluation($licitacion, $result);
                        return $result;
                    }
                } else {
                    Log::warning("Gemini API error {$response->status()}: " . substr($response->body(), 0, 200));
                }
            } catch (\Exception $e) {
                Log::warning("Gemini API call failed: " . $e->getMessage());
            }
        }

        // Si Gemini falla o no responde JSON válido, queda PENDIENTE (nunca 45% inventado)
        $pending = [
            'filtro_descarte' => null,
            'puntajes' => null,
            'score' => null,
            'nivel' => 'PENDIENTE',
            'etiqueta' => 'PENDIENTE',
            'lineas_tornus' => [],
            'antecedente_tornus' => null,
            'componente_aliado' => null,
            'categoria' => $licitacion->categoria ?: 'General',
            'dictamen' => 'Propuesta pendiente de análisis con pliegos de condiciones.',
            'punto_clave' => 'Re-evaluar cuando estén disponibles los documentos definitivos.',
            'estrategia_postulacion' => 'Por verificar'
        ];

        $this->saveEvaluation($licitacion, $pending);
        return $pending;
    }

    protected function saveEvaluation(Licitacion $licitacion, array $data): void
    {
        $licitacion->score = isset($data['score']) && $data['score'] !== null ? (int)$data['score'] : null;
        $licitacion->nivel = $data['nivel'] ?? 'PENDIENTE';
        $licitacion->etiqueta = $data['etiqueta'] ?? ($data['aplica'] ?? null);
        
        if (!empty($data['categoria'])) {
            $licitacion->categoria = $data['categoria'];
        }

        $licitacion->filtro_descarte = $data['filtro_descarte'] ?? null;
        $licitacion->subpuntajes = $data['puntajes'] ?? ($data['subpuntajes'] ?? null);
        $licitacion->antecedente_tornus = $data['antecedente_tornus'] ?? null;
        $licitacion->componente_aliado = $data['componente_aliado'] ?? null;

        $licitacion->justificacion = $data['dictamen'] ?? ($data['justificacion'] ?? null);
        $licitacion->puntos_clave = $data['punto_clave'] ?? ($data['puntos_clave'] ?? null);
        $licitacion->estrategia_postulacion = $data['estrategia_postulacion'] ?? ($data['etiqueta'] ?? null);

        // Mapear líneas afines para compatibilidad
        if (!empty($data['lineas_tornus']) && is_array($data['lineas_tornus'])) {
            $linesNames = [
                1 => 'Branding y marca de destino',
                2 => 'Inteligencia de mercados y datos turísticos',
                3 => 'Planeación estratégica de turismo',
                4 => 'Desarrollo creativo y producción de contenido',
                5 => 'Campañas, plan de medios y comunicaciones',
                6 => 'Fortalecimiento de capacidades turísticas'
            ];
            $sug = [];
            foreach ($data['lineas_tornus'] as $num) {
                if (isset($linesNames[$num])) {
                    $sug[] = $linesNames[$num];
                }
            }
            $licitacion->servicios_sugeridos = $sug;
        } elseif (isset($data['servicios_sugeridos'])) {
            $licitacion->servicios_sugeridos = (array)$data['servicios_sugeridos'];
        }

        $licitacion->evaluado_en = now();
        $licitacion->save();
    }
}
