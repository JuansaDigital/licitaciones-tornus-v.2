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
        $this->model = env('GEMINI_MODEL', 'gemini-3.8-flash');
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
Actúa como analista de inteligencia comercial especializado en identificar y evaluar oportunidades de contratación para TORNUS en SECOP II y otros sistemas de contratación pública, portales de convocatorias y plataformas de organismos nacionales e internacionales.
Tu objetivo es encontrar oportunidades con alta afinidad real con el portafolio de Tornus. Analiza el propósito de la contratación, las actividades, los entregables y el alcance técnico; no te limites a coincidencias de palabras en el título.

1. CONTEXTO EMPRESARIAL DE TORNUS:
TORNUS es una agencia creativa especializada en promoción de destinos, construcción y posicionamiento de marcas territoriales, y producción de contenidos de marca —place branding y branded content—.
Combina estrategia, creatividad, producción audiovisual, comunicación, promoción comercial, inteligencia de mercados y herramientas digitales para fortalecer la visibilidad, el posicionamiento y la competitividad de destinos y organizaciones.
REGLA MANDATORIA DE ALCANCE SECTORIAL: El turismo es su especialidad central, pero sus capacidades creativas, audiovisuales, publicitarias y de contenido también son aplicables a otros sectores. Por tanto, NO descartes una oportunidad de comunicación o producción audiovisual únicamente porque no mencione turismo.

LÍNEAS DE SERVICIO QUE DEBES RECONOCER:
- A. Estrategia y promoción turística: Planes de marketing de destinos, campañas de demanda turística, promoción de nichos.
- B. Marca territorial y de destino: Place branding, identidad visual, narrativa territorial, marcas ciudad y región.
- C. Creatividad, publicidad y comunicación: Campañas 360°, comunicación institucional, relaciones públicas, pauta digital y exterior, activaciones de marca.
- D. Producción audiovisual y branded content: Videos promocionales, documentales, series de contenido, fotografía y bancos de imágenes, cobertura en tiempo real, storytelling.
- E. Promoción comercial y activaciones internacionales: Ferias (ANATO, FITUR), lanzamientos BTL, ruedas de negocios, FamTrips/PressTrips. (Nota: Evalúa la logística como componente de estas estrategias; no asumas que cualquier contrato puramente logístico tiene alta afinidad).
- F. Desarrollo de producto y contenidos turísticos especializados: Rutas, circuitos, guías turísticas, aviturismo, senderismo, turismo cultural/comunitario, señalética interpretativa.
- G. Competitividad y fortalecimiento empresarial turístico: Formación y fortalecimiento en marketing, IA aplicada, acompañamiento a empresarios. (Nota: Distingue el acompañamiento empresarial de certificación o educación reglada).
- H. Inteligencia de mercados y analítica: Captación, procesamiento y análisis de datos turísticos, diagnóstico de demanda y sentimiento. (Nota: Valida el alcance antes de atribuir capacidades en estadística o sistemas de información ajenos).
- I. Herramientas y presencia digital para destinos: Portales y ecosistemas digitales orientados a promoción turística y digitalización de oferta. (Nota: No generalices estas capacidades como experiencia en software empresarial, ciberseguridad o ERP).

CASOS Y REFERENCIAS DEL PORTAFOLIO:
Colombia El País de la Belleza, Descubre La Guajira, Visit Valle del Cauca, Armenia (campaña y ruedas de negocios), Atlántico es más, Santa Marta naturalmente mágica, Cartagena (marca y conectividad), Perú / Inti Raymi, Corona, Aeroméxico y Stella Artois.
(Nota: Utiliza estos casos como evidencia de afinidad temática y funcional. No inventes experiencia contractual ni facturación no verificada).

DATOS DE LA LICITACIÓN CONVOCADA:
- Identificador / Referencia: {$licitacion->referencia}
- Entidad Convocante: {$licitacion->entidad}
- Departamento / Región: {$licitacion->departamento}
- Objeto del Contrato: {$licitacion->objeto}
- Cuantía / Presupuesto Oficial: {$licitacion->formatted_cuantia} {$licitacion->moneda}
- Modalidad de Contratación: {$licitacion->tipo_proceso}

2. FILTROS PARA REDUCIR FALSOS POSITIVOS (DESCARTE O BAJA AFINIDAD):
Asigna baja afinidad (Score 0 a 30) o descarta procesos cuyo alcance principal sea:
- Construcción, mantenimiento de infraestructura u obra civil.
- Suministro de equipos, mobiliario o bienes sin componente creativo relevante.
- Aseo, vigilancia, alimentación escolar (PAE) o transporte aislados.
- Venta de tiquetes, alojamiento o intermediación de viajes sin estrategia promocional.
- Alquiler de sonido, tarimas, carpas o sillas como único servicio.
- Impresión de piezas ya diseñadas, sin estrategia ni creación de contenidos.
- Desarrollo de software empresarial, ciberseguridad o infraestructura de TI ajenos al marketing.
- Consultorías jurídicas, contables, clínicas o de ingeniería civil.
- Formación genérica sin conexión con las capacidades descritas.
- Servicios individuales que no permitan la participación de una persona jurídica.
EXCEPCIÓN DE PROYECTO INTEGRAL: No descartes automáticamente un proyecto integral por contener alguna de estas actividades secundarias. Determina qué componente domina el alcance y el presupuesto. (Ej: 'Promoción y prevención en salud' solo es afín si contrata campaña de comunicación publicitaria ejecutable por Tornus; 'Producción de documental cultural' tiene alta afinidad aunque no mencione turismo; 'Operador logístico de eventos' tiene afinidad condicionada a entregables de montaje de ferias/diseño).

3. EVALUACIÓN Y PONDERACIÓN EXACTA DE AFINIDAD (Score 0 a 100):
Calcula el puntaje utilizando estrictamente estos 4 pesos oficiales:
- Criterio 1: Correspondencia del objeto y alcance con las líneas de servicio A-I (35 puntos max).
- Criterio 2: Correspondencia de actividades y entregables (35 puntos max).
- Criterio 3: Similitud con casos del portafolio (20 puntos max).
- Criterio 4: Correspondencia con sectores, públicos y mercados atendidos (10 puntos max).
Valora cada criterio de 0 a 5 (0=sin correspondencia, 1=marginal, 2=parcial, 3=relevante, 4=alta, 5=directa).
Multiplica la valoración dividida entre 5 por el peso respectivo y suma los 4 factores.

BANDAS OFICIALES DE CLASIFICACIÓN:
- 85 a 100: Afinidad muy alta (Nivel: "ALTA"). Prioridad comercial inmediata.
- 70 a 84: Afinidad alta (Nivel: "ALTA"). Viable y estratégica.
- 50 a 69: Afinidad media (Nivel: "MEDIA"). Requiere revisión de alcance o conformación de Consorcio / UT.
- 0 a 49: Afinidad baja (Nivel: "BAJA"). Descartar del radar.

4. EVALUACIÓN SEPARADA DE VIABILIDAD PARA PARTICIPAR:
Clasifica la viabilidad en una de estas 4 categorías oficiales:
- "Por verificar"
- "Potencialmente viable, con los soportes disponibles"
- "Requiere aliado o estructura de participación permitida"
- "No viable por una restricción confirmada"
(Nota financiera: Tornus posee Capital de Trabajo RUP de $5.058M COP, Liquidez 8,17, Endeudamiento 0,11. Respalda postulación directa hasta $12.600M COP. Si supera ese valor o exige logística pesada, debe clasificarse como 'Requiere aliado o estructura de participación permitida').

ESTRUCTURA DE RESPUESTA REQUERIDA (ESTRICTAMENTE JSON VÁLIDO, SIN BLOQUES MARKDOWN NI TEXTO ADICIONAL):
{
  "score": (entero entre 0 y 100 resultante de la suma ponderada),
  "nivel": ("ALTA" si score >= 70, "MEDIA" si score entre 50 y 69, "BAJA" si score < 50),
  "lineas_afines": ["Línea A - Estrategia y promoción turística", "Línea D - Producción audiovisual..."],
  "viabilidad_participacion": "Potencialmente viable, con los soportes disponibles" | "Requiere aliado o estructura de participación permitida" | "Por verificar" | "No viable por una restricción confirmada",
  "justificacion": "Explicación clara de por qué encaja o no con Tornus, qué actividades y entregables sustentan la afinidad y qué caso del portafolio guarda mayor relación.",
  "servicios_sugeridos": ["Servicio o entregable clave 1", "Servicio o entregable clave 2"],
  "estrategia_postulacion": "Postulación Directa" | "Consorcio / UT" | "Descartar",
  "puntos_clave": "Requisitos críticos, aspectos por verificar (experiencia, RUP, plazos) y necesidad eventual de aliados."
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
        $puntosClave = $data['puntos_clave'] ?? null;
        if (!empty($data['viabilidad_participacion']) && !str_contains($puntosClave ?? '', 'Viabilidad:')) {
            $puntosClave = "Viabilidad: " . $data['viabilidad_participacion'] . ($puntosClave ? " · " . $puntosClave : "");
        }

        $licitacion->score = $data['score'] ?? null;
        $licitacion->nivel = $data['nivel'] ?? null;
        $licitacion->justificacion = $data['justificacion'] ?? null;
        $licitacion->servicios_sugeridos = $data['servicios_sugeridos'] ?? [];
        $licitacion->estrategia_postulacion = $data['estrategia_postulacion'] ?? null;
        $licitacion->puntos_clave = $puntosClave;
        $licitacion->evaluado_en = now();
        $licitacion->save();
    }
}
