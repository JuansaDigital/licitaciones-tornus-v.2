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
     * Evalúa una licitación contra el perfil corporativo integral de Tornus S.A.S.
     */
    public function evaluate(Licitacion $licitacion): array
    {
        $profileJson = json_encode($this->agencyProfile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<EOT
Eres el Director de Licitaciones y Desarrollo de Negocios de TORNUS S.A.S. (Tornus Agency).
Tu tarea es analizar exhaustivamente la siguiente oportunidad de contratación pública (SECOP II Colombia o SEACE Perú) y emitir un dictamen de afinidad estratégica, técnica y financiera, utilizando el perfil corporativo y la matriz de emparejamiento de Tornus.

BASE DE CONOCIMIENTO Y PERFIL CORPORATIVO DE TORNUS S.A.S.:
{$profileJson}

DATOS DE LA LICITACIÓN CONVOCADA:
- Identificador / Referencia: {$licitacion->referencia}
- Entidad Convocante: {$licitacion->entidad}
- Departamento / Región: {$licitacion->departamento}
- Objeto del Contrato: {$licitacion->objeto}
- Cuantía / Presupuesto Oficial: {$licitacion->formatted_cuantia} {$licitacion->moneda}
- Modalidad de Contratación: {$licitacion->tipo_proceso}

REGLAS MANDATORIAS DE EVALUACIÓN (RÚBRICA OFICIAL):
1. CRITERIOS DE EXCLUSIÓN TOTAL (Score 0):
   Si el objeto contractual o su núcleo principal corresponde a: obras civiles, vías, pavimentación, acueductos, vigilancia, seguridad física, aseo, cafetería, insumos médicos/hospitalarios, alimentación escolar (PAE), suministro de combustible, vehículos o maquinaria pesada, o interventoría de obras civiles: Asigna inmediatamente Score = 0, Nivel = "BAJA", Estrategia = "Descartar".

2. PONDERACIÓN OFICIAL DE AFINIDAD (Score 0 a 100):
   - 35%: Correspondencia del objeto y alcance con las Líneas de Servicio A a I (Estrategia y promoción turística, Marca territorial/Place branding, Creatividad y comunicación 360°, Producción audiovisual cine/digital, Promoción comercial/Ferias/BTL/FamTrips, Desarrollo de producto turístico/Rutas/Señalética, Competitividad/Capacitación/IA, Inteligencia de mercados/Big Data, Herramientas digitales/Portales de destino).
   - 35%: Correspondencia de actividades y entregables específicos (diagnósticos, conceptos de campaña, bancos audiovisuales, montajes de stands, planes de medios, guías impresas/digitales, apps, etc.).
   - 20%: Similitud con casos emblemáticos del portafolio (Colombia el país de la belleza, Descubre La Guajira, Visit Valle, Pueblos Mágicos, Armenia corazón de Colombia, Atlántico es más, Santa Marta naturalmente mágica, Inti Raymi Perú, Corona, Aeroméxico).
   - 10%: Correspondencia con los sectores y entidades objetivo (Turismo, Cultura, Desarrollo Económico, FONTUR, Viceministerio, Alcaldías/Gobernaciones turísticas, Bureaus, Cámaras de Comercio).

3. BANDAS DE DECISIÓN:
   - 85 a 100: "ALTA" (Afinidad Muy Alta - Prioridad comercial inmediata. Convocar comité técnico).
   - 70 a 84: "ALTA" (Afinidad Alta - Oportunidad plenamente viable y estratégica).
   - 50 a 69: "MEDIA" (Afinidad Media - Procesos con componentes mixtos o complementarios que aconsejan Consorcio o Unión Temporal).
   - 0 a 49: "BAJA" (Afinidad Baja - Descartar del radar comercial).

4. CAPACIDAD FINANCIERA Y POSTULACIÓN:
   - Tornus S.A.S. posee un Capital de Trabajo en RUP de $5.058.026.000 COP, Liquidez de 8,17 y Endeudamiento de 0,11.
   - Respalda postulaciones directas de hasta $12.600M COP.
   - Si la cuantía supera $12.000M COP o el pliego incluye componentes logísticos masivos o transversales fuera del core creativo/promocional, recomienda estrategia "Consorcio / UT" señalando el perfil del socio complementario.

ESTRUCTURA DE RESPUESTA REQUERIDA (ESTRICTAMENTE JSON VÁLIDO, SIN BLOQUES MARKDOWN NI COMENTARIOS):
{
  "score": (entero entre 0 y 100),
  "nivel": ("ALTA" si score >= 70, "MEDIA" si score entre 50 y 69, "BAJA" si score < 50),
  "lineas_afines": ["Línea A - Estrategia y promoción turística", "Línea D - Producción audiovisual..."],
  "justificacion": "Texto explicativo ejecutivo y fundamentado de 1 a 2 párrafos que analice el encaje con las líneas A-I, portafolio y viabilidad para Tornus.",
  "servicios_sugeridos": ["Servicio o entregable clave 1", "Servicio o entregable clave 2"],
  "estrategia_postulacion": "Postulación Directa" o "Consorcio / UT" o "Descartar",
  "puntos_clave": "Puntos críticos habilitantes (financieros, RUP, plazos, personal senior clave) a considerar en el pliego."
}
EOT;

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

        // Fallback Heurístico determinista alineado con la rúbrica oficial de 4 factores
        $fallback = $this->heuristicEvaluation($licitacion);
        $this->saveEvaluation($licitacion, $fallback);
        return $fallback;
    }

    /**
     * Motor heurístico determinista de afinidad fundamentado en los 4 factores oficiales
     */
    protected function heuristicEvaluation(Licitacion $licitacion): array
    {
        $text = mb_strtoupper($licitacion->objeto . ' ' . $licitacion->entidad . ' ' . $licitacion->categoria . ' ' . $licitacion->departamento, 'UTF-8');
        
        $exclusions = $this->agencyProfile['criterios_exclusion'] ?? [
            'OBRA CIVIL', 'PAVIMENTACIÓN', 'ACUEDUCTO', 'ALCANTARILLADO', 'VIGILANCIA', 'SEGURIDAD PRIVADA',
            'ASEO', 'LIMPIEZA', 'CAFETERÍA', 'MEDICAMENTO', 'HOSPITALARIO', 'QUIRÚRGICO',
            'ALIMENTACIÓN ESCOLAR', 'PAE', 'COMBUSTIBLE', 'MAQUINARIA PESADA', 'PARQUE AUTOMOTOR',
            'INTERVENTORÍA DE OBRA'
        ];

        // 1. Descarte inmediato por exclusión total
        foreach ($exclusions as $ex) {
            $normalizedEx = mb_strtoupper(trim($ex), 'UTF-8');
            $words = preg_split('/[\s,]+/', $normalizedEx);
            foreach ($words as $w) {
                if (strlen($w) >= 5 && str_contains($text, $w)) {
                    // Evitar falsos positivos si la palabra es común en turismo
                    if (in_array($w, ['SERVICIOS', 'SUMINISTRO', 'PROGRAMA', 'APOYO', 'GESTIÓN'])) {
                        continue;
                    }
                    return [
                        'score' => 0,
                        'nivel' => 'BAJA',
                        'aplica' => 'NO APLICA',
                        'lineas_afines' => [],
                        'justificacion' => "El proceso corresponde a actividades de {$w} o conexas, las cuales constituyen un criterio de exclusión total para Tornus S.A.S. No presenta componentes de promoción de destinos, creatividad, comunicaciones ni desarrollo turístico.",
                        'servicios_sugeridos' => [],
                        'estrategia_postulacion' => 'Descartar',
                        'puntos_clave' => 'Objeto contractual incompatible con el Registro Único de Proponentes (RUP) y el core estratégico de la agencia.'
                    ];
                }
            }
        }

        // Factor 1: Correspondencia Objeto y Alcance con Líneas A a I (Máximo 35 puntos)
        $factor1 = 0;
        $matchedLines = [];

        $linesMap = [
            'Línea A - Estrategia y Promoción Turística' => ['PROMOCIÓN TURÍSTICA', 'DESTINO', 'MERCADEO TURÍSTICO', 'REACTIVACIÓN TURÍSTICA', 'ATRACCIÓN DE VISITANTES', 'PLAN DE MARKETING'],
            'Línea B - Marca Territorial (Place Branding)' => ['MARCA CIUDAD', 'MARCA REGIÓN', 'MARCA DESTINO', 'PLACE BRANDING', 'IDENTIDAD VISUAL', 'NARRATIVA DE MARCA'],
            'Línea C - Creatividad y Comunicación 360°' => ['CAMPAÑA PUBLICITARIA', 'CREATIVIDAD', 'PLAN DE MEDIOS', 'PAUTA', 'DIFUSIÓN INSTITUCIONAL', 'COMUNICACIONES'],
            'Línea D - Producción Audiovisual Cine/Digital' => ['AUDIOVISUAL', 'FOTOGRAFÍA', 'BANCO DE IMÁGENES', 'PRODUCCIÓN DE VIDEO', 'SPOT', 'RODAJE', 'DOCUMENTAL'],
            'Línea E - Promoción Comercial, Ferias y BTL' => ['FERIAS', 'ANATO', 'FITUR', 'EVENTO', 'BTL', 'RUEDA DE NEGOCIOS', 'FAMTRIP', 'PRESSTRIP', 'STAND'],
            'Línea F - Desarrollo de Producto Turístico' => ['RUTA TURÍSTICA', 'GUÍA TURÍSTICA', 'AVITURISMO', 'TURISMO COMUNITARIO', 'SEÑALÉTICA', 'GASTRONOMÍA'],
            'Línea G - Competitividad y Capacitación' => ['FORTALECIMIENTO', 'CAPACITACIÓN', 'ASISTENCIA TÉCNICA', 'TALLERES', 'PRESTADORES TURÍSTICOS'],
            'Línea H - Inteligencia de Mercados y Analítica' => ['INTELIGENCIA DE MERCADO', 'BIG DATA', 'ANALÍTICA', 'PERFIL DEL VISITANTE', 'OBSERVATORIO'],
            'Línea I - Plataformas y Presencia Digital' => ['PORTAL WEB', 'PÁGINA WEB', 'APLICACIÓN MÓVIL', 'APP', 'DIGITALIZACIÓN', 'PLATAFORMA INTERACTIVA']
        ];

        foreach ($linesMap as $lineName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $factor1 = min(35, $factor1 + 12);
                    $matchedLines[] = $lineName;
                    break;
                }
            }
        }
        $matchedLines = array_values(array_unique($matchedLines));

        // Factor 2: Correspondencia Actividades y Entregables (Máximo 35 puntos)
        $factor2 = 0;
        $deliverablesKeywords = [
            'CAMPAÑA' => 8, 'PIEZAS' => 7, 'CONTENIDO' => 7, 'PUBLICIDAD' => 7,
            'VIDEO' => 8, 'FOTO' => 6, 'DISEÑO' => 7, 'REDES SOCIALES' => 7,
            'STAND' => 8, 'LOGÍSTICA' => 6, 'OPERACIÓN' => 6, 'MATERIAL PROMOCIONAL' => 7,
            'ESTRATEGIA' => 8, 'DIAGNÓSTICO' => 6, 'PLAN' => 6, 'PORTAL' => 8
        ];
        foreach ($deliverablesKeywords as $deliv => $points) {
            if (str_contains($text, $deliv)) {
                $factor2 = min(35, $factor2 + $points);
            }
        }

        // Factor 3: Similitud con Casos del Portafolio y Destinos (Máximo 20 puntos)
        $factor3 = 0;
        $portfolioKeywords = [
            'TURISMO' => 10, 'DESTINO' => 8, 'CULTURA' => 6, 'PATRIMONIO' => 6,
            'VALLE DEL CAUCA' => 8, 'GUAJIRA' => 8, 'ATLÁNTICO' => 8, 'SANTA MARTA' => 8,
            'BOGOTÁ' => 6, 'ARMENIA' => 8, 'CUSCO' => 8, 'PAÍS DE LA BELLEZA' => 10
        ];
        foreach ($portfolioKeywords as $pkw => $points) {
            if (str_contains($text, $pkw)) {
                $factor3 = min(20, $factor3 + $points);
            }
        }

        // Factor 4: Sector y Entidad Objetivo (Máximo 10 puntos)
        $factor4 = 0;
        $entityKeywords = [
            'FONTUR' => 10, 'MINCIT' => 10, 'PROCOLOMBIA' => 10, 'PROMPERU' => 10,
            'IDT' => 9, 'BUREAU' => 9, 'TURISMO' => 8, 'DESARROLLO ECONÓMICO' => 7,
            'CULTURA' => 6, 'CÁMARA DE COMERCIO' => 8, 'GOBERNACIÓN' => 5, 'ALCALDÍA' => 4
        ];
        foreach ($entityKeywords as $ekw => $points) {
            if (str_contains($text, $ekw)) {
                $factor4 = min(10, $factor4 + $points);
            }
        }

        // Sumatoria total (0 - 100)
        $score = $factor1 + $factor2 + $factor3 + $factor4;

        // Si no hubo ningún cruce con líneas de servicio ni entregables, el score base es mínimo
        if ($factor1 === 0 && $factor2 === 0) {
            $score = min(25, $score);
        }

        $cuantia = (float)($licitacion->cuantia ?? 0);
        $capitalTrabajo = 5058026000; // $5.058M COP

        if ($score >= 85) {
            $nivel = 'ALTA';
            $aplica = 'APLICA';
            $estrategia = ($cuantia > 10000000000) ? 'Consorcio / UT' : 'Postulación Directa';
            $justificacion = "Oportunidad con afinidad muy alta (Score {$score}/100). Presenta correspondencia directa con " . (!empty($matchedLines) ? implode(' y ', array_slice($matchedLines, 0, 2)) : 'las líneas estratégicas de Tornus') . ", ajustándose de forma precisa al portafolio y capacidades operativas y creativas acreditadas en el RUP.";
            $puntosClave = "Revisar los índices financieros habilitantes (Tornus cuenta con liquidez 8,17 y endeudamiento 0,11) y estructurar el equipo senior de creativos y directores de cuenta requeridos.";
        } elseif ($score >= 70) {
            $nivel = 'ALTA';
            $aplica = 'APLICA';
            $estrategia = ($cuantia > 8000000000) ? 'Consorcio / UT' : 'Postulación Directa';
            $justificacion = "Oportunidad viable y estratégica (Score {$score}/100). El objeto y alcance coinciden con la propuesta de valor de Tornus en comunicación, mercadeo y posicionamiento. Existe alta probabilidad de éxito en la formulación de propuesta.";
            $puntosClave = "Verificar plazos de ejecución, requerimientos de personal en territorio y acreditación de experiencia en SMMLV frente a los 20 contratos del RUP.";
        } elseif ($score >= 50) {
            $nivel = 'MEDIA';
            $aplica = 'APLICA CON CONDICIONES';
            $estrategia = 'Consorcio / UT';
            $justificacion = "Oportunidad con afinidad media (Score {$score}/100). Contiene actividades que cruzan con las capacidades de Tornus, pero combina componentes que sugieren la conformación de un Consorcio o Unión Temporal con un socio local u operativo.";
            $puntosClave = "Evaluar el peso del componente creativo frente a la logística o suministros, y definir el porcentaje de participación conveniente.";
        } else {
            $nivel = 'BAJA';
            $aplica = 'NO APLICA';
            $estrategia = 'Descartar';
            $justificacion = "Oportunidad con afinidad baja (Score {$score}/100). No evidencia una correlación sustancial con el core de negocio, líneas de servicio especializadas ni casos de éxito de Tornus S.A.S.";
            $puntosClave = "No se sugiere destinar esfuerzos de costeo ni estructuración para esta convocatoria.";
        }

        $servicios = !empty($matchedLines) 
            ? array_map(function($line) { return explode(' - ', $line)[1] ?? $line; }, $matchedLines)
            : ['Estrategia y promoción de destinos', 'Creatividad y difusión publicitaria'];

        return [
            'score' => $score,
            'nivel' => $nivel,
            'aplica' => $aplica,
            'lineas_afines' => $matchedLines,
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
