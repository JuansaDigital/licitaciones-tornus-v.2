<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerfilTornusController extends Controller
{
    public const DEFAULT_PROFILE = [
        'nombre_agencia' => 'TORNUS S.A.S.',
        'marca_comercial' => 'Tornus Agency',
        'nit' => '901.328.143-2',
        'domicilio' => 'Cali, Valle del Cauca, Colombia',
        'direccion' => 'Carrera 68 No. 13B-61, Oficina 903A',
        'sitio_web' => 'https://tornus.agency',
        'redes_sociales' => [
            'instagram' => 'https://instagram.com/tornusagency',
            'linkedin' => 'https://linkedin.com/company/tornusagency',
        ],
        'constitucion' => [
            'fecha' => '2019-10-02',
            'camara_comercio' => 'Cali',
            'tamano_empresarial' => 'Mediana empresa',
            'regimen' => 'Empresa de Economía Naranja (Industrias Creativas y Culturales)',
            'representante_legal_principal' => 'Dagoberto Ospina Ospina',
            'representante_legal_suplente' => 'Dagoberto Ospina Jaramillo',
            'controlante' => 'David Alejandro Ospina Ospina',
        ],
        'rup' => [
            'numero' => '132704',
            'camara' => 'Cámara de Comercio de Cali',
            'contratos_inscritos' => 20,
            'experiencia_total_smmlv' => 11998.0,
            'contratos_mayores_700_smmlv' => 6,
        ],
        'capacidad_financiera' => [
            'corte' => '31/12/2025 (Certificado RUP vigente)',
            'activo_corriente_cop' => 5763513000,
            'activo_total_cop' => 6413513000,
            'pasivo_corriente_cop' => 705487000,
            'pasivo_total_cop' => 705487000,
            'patrimonio_neto_cop' => 5708026000,
            'capital_de_trabajo_cop' => 5058026000,
            'utilidad_operacional_cop' => 3811938000,
            'indicadores' => [
                'indice_liquidez' => 8.17,
                'indice_endeudamiento' => 0.11,
                'cobertura_intereses' => 76.70,
                'rentabilidad_patrimonio_roe' => 0.67,
                'rentabilidad_activo_roa' => 0.59,
            ],
            'capacidad_procesos' => [
                'respaldo_al_40_pct_capital_trabajo' => '$12.645.000.000 COP',
                'respaldo_al_20_pct_capital_trabajo' => '$25.290.000.000 COP',
                'nota' => 'Capacidad óptima para liderar licitaciones directas de hasta $12.000M COP y procesos de mayor envergadura en Consorcio o Unión Temporal.',
            ],
        ],
        'propuesta_valor' => 'Tornus S.A.S. es una agencia integral de estrategia, creatividad, marketing de destinos, branding territorial y producción de contenidos de alto impacto para turismo y desarrollo económico. Combina estrategia de alto nivel, creatividad publicitaria de estándar multinacional (leones de Cannes, Effie), producción audiovisual cinematográfica y sólida ejecución logística en territorio y eventos promocionales de alcance global.',
        'posicionamiento_sintetico' => 'Especialistas en turismo, marca territorial, promoción cultural y proyectos de desarrollo económico territorial. No es constructora, no suministra alimentos ni vigilancia. Su valor está en estrategia de alto nivel, creatividad publicitaria de red multinacional, producción audiovisual y ejecución territorial.',
        'lineas_servicio' => [
            'A' => [
                'nombre' => 'Estrategia y promoción turística',
                'descripcion' => 'Diseño e implementación de estrategias integrales de mercadeo de destinos, planes de marketing turístico, campañas de posicionamiento y reactivación turística.',
                'palabras_clave' => ['Promoción turística', 'posicionamiento de destino', 'estrategia de mercadeo turístico', 'plan de marketing turístico', 'campaña de destino', 'reactivación turística', 'atracción de visitantes'],
                'entregables' => ['Diagnóstico turístico', 'Plan estratégico de mercadeo', 'Concepto de campaña', 'Plan de medios', 'Piezas promocionales', 'Informes de impacto'],
                'codigos_unspsc' => ['80141600', '80141500', '93141800', '80171600'],
            ],
            'B' => [
                'nombre' => 'Marca territorial y de destino (place branding)',
                'descripcion' => 'Creación, reposicionamiento y manualización de identidades de marca ciudad, marca región, marca destino y arquitectura de marca territorial.',
                'palabras_clave' => ['Marca ciudad', 'marca región', 'marca destino', 'place branding', 'identidad visual territorial', 'manual de identidad', 'narrativa de marca', 'arquitectura de marca territorial'],
                'entregables' => ['Diagnóstico de percepción e identidad', 'Concepto de marca', 'Diseño de identificador y sistema gráfico', 'Manual de marca', 'Aplicaciones', 'Estrategia de adopción comunitaria'],
                'codigos_unspsc' => ['80141600', '82141500', '82141600', '80141500'],
            ],
            'C' => [
                'nombre' => 'Creatividad, publicidad y comunicación',
                'descripcion' => 'Campañas publicitarias 360°, creatividad publicitaria de estándar de red internacional, redacción publicitaria, planes de comunicaciones y difusión.',
                'palabras_clave' => ['Campaña publicitaria', 'creatividad publicitaria', 'diseño gráfico', 'redacción publicitaria', 'plan de comunicaciones', 'difusión institucional', 'sensibilización ciudadana', 'pauta publicitaria'],
                'entregables' => ['Conceptos creativos', 'Key visuals', 'Guiones', 'Piezas para medios masivos y digitales', 'Plan y compra de medios', 'Medición de alcance y engagement'],
                'codigos_unspsc' => ['82101500', '82101800', '82141500', '80141600'],
            ],
            'D' => [
                'nombre' => 'Producción audiovisual y branded content',
                'descripcion' => 'Producción audiovisual profesional con calidad cinematográfica, bancos fotográficos en alta resolución, documentales, spots y cápsulas digitales.',
                'palabras_clave' => ['Producción audiovisual', 'videos promocionales', 'banco de imágenes', 'fotografía profesional', 'documental', 'spots publicitarios', 'cápsulas de video', 'cobertura audiovisual'],
                'entregables' => ['Banco de fotos en alta resolución con cesión de derechos', 'Videos promocionales formato cine/digital', 'Reels y cápsulas temáticas', 'Masterización y postproducción profesional'],
                'codigos_unspsc' => ['82131600', '82131500', '82111500', '82101500'],
            ],
            'E' => [
                'nombre' => 'Promoción comercial y activaciones internacionales (BTL, ferias, ruedas)',
                'descripcion' => 'Operación de pabellones en ferias internacionales (ANATO, FITUR), activaciones de marca BTL, agendas comerciales, FamTrips y PressTrips.',
                'palabras_clave' => ['Ferias turísticas', 'ANATO', 'FITUR', 'misiones comerciales', 'activaciones de marca', 'eventos BTL', 'ruedas de negocios', 'stands promocionales', 'FamTrips', 'PressTrips'],
                'entregables' => ['Diseño y montaje de stands', 'Producción general de evento', 'Convocatoria de operadores', 'Agendas de negocios', 'Material promocional', 'Gestión logística e informes de negocios'],
                'codigos_unspsc' => ['80141600', '80141900', '90151800', '82101800'],
            ],
            'F' => [
                'nombre' => 'Desarrollo de producto y contenidos turísticos especializados',
                'descripcion' => 'Estructuración de rutas turísticas (aviturismo, senderismo, turismo cultural, gastronómico, comunitario), cartillas, guías y señalética interpretativa.',
                'palabras_clave' => ['Rutas turísticas', 'guías de turismo', 'aviturismo', 'senderismo', 'turismo comunitario', 'turismo cultural', 'turismo gastronómico', 'cartillas', 'señalética interpretativa'],
                'entregables' => ['Guías turísticas impresas y digitales', 'Mapas de rutas', 'Manuales de producto', 'Diseño de señalética interpretativa', 'Fichas de atractivos', 'Banco de contenidos'],
                'codigos_unspsc' => ['80141600', '82121500', '93141800', '55101500'],
            ],
            'G' => [
                'nombre' => 'Competitividad y fortalecimiento empresarial turístico',
                'descripcion' => 'Programas de formación, asistencia técnica, adopción de inteligencia artificial aplicada al turismo y buenas prácticas para empresarios turísticos.',
                'palabras_clave' => ['Fortalecimiento empresarial', 'capacitación a prestadores turísticos', 'talleres', 'asistencia técnica', 'adopción tecnológica', 'IA aplicada al turismo', 'buenas prácticas'],
                'entregables' => ['Programas de formación', 'Metodologías de transferencia', 'Talleres presenciales y virtuales', 'Kits de herramientas para empresarios', 'Memorias y certificaciones'],
                'codigos_unspsc' => ['86101600', '86101700', '80101500', '80141600'],
            ],
            'H' => [
                'nombre' => 'Inteligencia de mercados y analítica',
                'descripcion' => 'Analítica de Big Data turístico, estudios de demanda, perfil del visitante, percepción de destino, social listening y tableros interactivos.',
                'palabras_clave' => ['Inteligencia de mercados', 'analítica de turismo', 'Big Data turístico', 'estudios de demanda', 'perfil del visitante', 'percepción de destino', 'tableros de control', 'social listening'],
                'entregables' => ['Informes de inteligencia de mercado', 'Dashboards interactivos', 'Análisis de sentimiento y escucha digital', 'Recomendaciones estratégicas basadas en datos'],
                'codigos_unspsc' => ['80141500', '81112000', '80101500', '80141600'],
            ],
            'I' => [
                'nombre' => 'Herramientas y presencia digital para destinos',
                'descripcion' => 'Desarrollo de portales web turísticos, aplicaciones móviles interactivas, mapas georreferenciados, audioguías y digitalización de inventario turístico.',
                'palabras_clave' => ['Portales web turísticos', 'sitios de destino', 'aplicaciones móviles de turismo', 'experiencias interactivas', 'audioguías', 'digitalización de oferta'],
                'entregables' => ['Sitios web responsivos y accesibles', 'Apps nativas o webapps', 'Integración de mapas y geolocalización', 'CMS administrables', 'Analítica digital configurada'],
                'codigos_unspsc' => ['81111500', '81112100', '82141500', '80141600'],
            ],
        ],
        'servicios_principales' => [
            'Estrategia y promoción turística nacional e internacional',
            'Diseño e implementación de marca territorial, marca ciudad y place branding',
            'Creatividad publicitaria 360°, key visuals y campañas de difusión masiva',
            'Producción audiovisual cinematográfica, fotografía profesional y banco de contenidos',
            'Diseño, montaje de stands y operación de eventos promocionales, ferias (ANATO/FITUR) y BTL',
            'Desarrollo de producto turístico, guías especializadas y señalética interpretativa',
            'Fortalecimiento empresarial, capacitación e inteligencia artificial aplicada al turismo',
            'Inteligencia de mercados, Big Data turístico y tableros de analítica de demanda',
            'Desarrollo de portales web y plataformas digitales interactivas para destinos',
        ],
        'experiencia_rup' => [
            [
                'contrato' => 'LP-006-2025 Gobernación de La Guajira',
                'entidad' => 'Gobernación de La Guajira',
                'cuantia_cop' => 6355000000,
                'smmlv' => 3629.80,
                'objeto' => 'Estrategia integral de promoción turística, posicionamiento de la marca Descubre La Guajira, producción de banco audiovisual de alta calidad, campañas publicitarias y activaciones BTL nacionales e internacionales.',
                'estado' => 'Liquidado / Acreditado RUP',
            ],
            [
                'contrato' => 'Convenio de Promoción Cali-Valle Bureau',
                'entidad' => 'Cali-Valle Bureau',
                'cuantia_cop' => 2380000000,
                'smmlv' => 1362.96,
                'objeto' => 'Promoción, mercadeo y posicionamiento del destino Cali y Valle del Cauca a nivel nacional e internacional.',
                'estado' => 'Liquidado / Acreditado RUP',
            ],
            [
                'contrato' => 'Cámara de Comercio de Santa Marta',
                'entidad' => 'Cámara de Comercio de Santa Marta para el Magdalena',
                'cuantia_cop' => 2170000000,
                'smmlv' => 1242.65,
                'objeto' => 'Fortalecimiento de la competitividad turística y estrategia de promoción del destino Magdalena.',
                'estado' => 'Liquidado / Acreditado RUP',
            ],
            [
                'contrato' => 'INDETUR Santa Marta',
                'entidad' => 'Instituto Distrital de Turismo de Santa Marta (INDETUR)',
                'cuantia_cop' => 2110000000,
                'smmlv' => 1206.31,
                'objeto' => 'Campañas de promoción turística de temporada, producción de banco de contenidos y posicionamiento de Santa Marta.',
                'estado' => 'Liquidado / Acreditado RUP',
            ],
            [
                'contrato' => 'Cotelco Capítulo Atlántico / FONTUR',
                'entidad' => 'Asociación Hotelera y Turística de Colombia - Cotelco Atlántico',
                'cuantia_cop' => 1950000000,
                'smmlv' => 1115.48,
                'objeto' => 'Estrategia de reactivación turística, mercadeo de destino y fortalecimiento de la oferta hotelera.',
                'estado' => 'Liquidado / Acreditado RUP',
            ],
            [
                'contrato' => 'Gobernación del Valle del Cauca - Pueblos Mágicos',
                'entidad' => 'Gobernación del Valle del Cauca',
                'cuantia_cop' => 1290000000,
                'smmlv' => 736.56,
                'objeto' => 'Estrategia de marca territorial, señalética interpretativa y promoción de municipios con vocación turística singular.',
                'estado' => 'Liquidado / Acreditado RUP',
            ],
        ],
        'convenios_en_ejecucion' => [
            [
                'entidad' => 'Telecafé / FONTUR',
                'cuantia_cop' => 25333000000,
                'objeto' => 'Operación de estrategias de promoción turística de gran escala a nivel nacional.',
            ],
            [
                'entidad' => 'Cali-Valle Bureau / FONTUR',
                'cuantia_cop' => 25186000000,
                'objeto' => 'Convenio de promoción y posicionamiento regional y macrodestinos.',
            ],
        ],
        'portafolio_referencia' => [
            [
                'caso' => 'Colombia, el país de la belleza',
                'cliente' => 'MinCIT - FONTUR',
                'alcance' => 'Creación de narrativa de marca país, producción de piezas promocionales y activaciones para el posicionamiento turístico nacional e internacional.',
            ],
            [
                'caso' => 'Descubre La Guajira',
                'cliente' => 'Gobernación de La Guajira',
                'alcance' => 'Estrategia integral de promoción, marca territorial, banco de más de 1.000 fotos y 50 videos cine, eventos BTL y activaciones en ferias internacionales.',
            ],
            [
                'caso' => 'Visit Valle del Cauca',
                'cliente' => 'Gobernación del Valle del Cauca',
                'alcance' => 'Campaña de destino, banco audiovisual, piezas promocionales y presencia en vitrinas turísticas nacionales e internacionales.',
            ],
            [
                'caso' => 'Pueblos Mágicos del Valle del Cauca',
                'cliente' => 'Gobernación del Valle del Cauca',
                'alcance' => 'Estrategia de marca territorial, concepto gráfico y campañas de promoción para municipios turísticos singulares.',
            ],
            [
                'caso' => 'Armenia, el corazón de Colombia',
                'cliente' => 'Alcaldía de Armenia',
                'alcance' => 'Desarrollo de marca ciudad, narrativa estratégica, sistema gráfico, manual de marca y campaña de lanzamiento multicanal.',
            ],
            [
                'caso' => 'Atlántico es más',
                'cliente' => 'Gobernación del Atlántico',
                'alcance' => 'Estrategia de comunicación turística y promoción de los atractivos culturales, gastronómicos y naturales del departamento.',
            ],
            [
                'caso' => 'Santa Marta - Naturalmente Mágica',
                'cliente' => 'Cámara de Comercio e INDETUR Santa Marta',
                'alcance' => 'Campañas de promoción de temporada vacacional, banco audiovisual y posicionamiento en canales digitales.',
            ],
            [
                'caso' => 'Inti Raymi (Perú)',
                'cliente' => 'Promoción Cultural Internacional',
                'alcance' => 'Registro audiovisual cinematográfico y campaña de posicionamiento cultural internacional de la festividad insigne del Cusco.',
            ],
            [
                'caso' => 'Marcas Globales (Corona, Aeroméxico, Stella Artois)',
                'cliente' => 'Cerveza Corona / Aeroméxico / Stella Artois',
                'alcance' => 'Corona Sunsets y activaciones de sostenibilidad; campañas de conectividad y destino de Aeroméxico; eventos y experiencias gastronómicas de Stella Artois.',
            ],
        ],
        'equipo_senior' => [
            [
                'nombre' => 'Julio César Herazo Romero',
                'cargo' => 'Director General',
                'perfil' => 'Ex Vicepresidente de Ogilvy & Mather. Más de 20 años de experiencia en estrategia de marca, comunicación masiva y dirección de agencias multinacionales.',
            ],
            [
                'nombre' => 'Oswaldo Daniel Rincón',
                'cargo' => 'Director Creativo',
                'perfil' => 'Trayectoria en agencias globales (Ogilvy, Leo Burnett). Experiencia en marcas como Bavaria/AB InBev, Coca-Cola y destinos turísticos.',
            ],
            [
                'nombre' => 'Juan Harly Cárdenas',
                'cargo' => 'Director General Creativo',
                'perfil' => 'Premiado en festivales internacionales (Cannes Lions, El Ojo de Iberoamérica, Effie). Ex Director Creativo en Grey, McCann y Ogilvy.',
            ],
            [
                'nombre' => 'Andrés Camilo Núñez',
                'cargo' => 'Director de Copywriting y Contenidos',
                'perfil' => 'Especialista en narrativa de marca, storytelling y redacción publicitaria de impacto. Ex copywriter senior en VML y agencias de red.',
            ],
            [
                'nombre' => 'Angélica Garzón',
                'cargo' => 'Directora de Prensa y Relaciones Públicas',
                'perfil' => 'Periodista especializada en turismo y comunicación institucional. Ex jefe de prensa de ANATO nacional.',
            ],
            [
                'nombre' => 'Natalia Forero',
                'cargo' => 'Gerente de Proyectos',
                'perfil' => 'Profesional en gobierno y relaciones internacionales. Amplia experiencia en gerencia de proyectos públicos y convenios interinstitucionales (FONTUR, MinCIT).',
            ],
            [
                'nombre' => 'María Juliana Espinosa',
                'cargo' => 'Directora de Planificación Estratégica (Strategic Planner)',
                'perfil' => 'Especialista en investigación del consumidor y estrategia de comunicación. Trayectoria en Ogilvy y Buentipo.',
            ],
            [
                'nombre' => 'Nicolás Segura',
                'cargo' => 'Director de Cuentas y Operaciones',
                'perfil' => 'Gestión de proyectos complejos, relacionamiento institucional y cumplimiento contractual en contratación pública. Ex supervisor de cuentas en VMLY&R.',
            ],
            [
                'nombre' => 'Germán Cardoso',
                'cargo' => 'Director de Producción y Eventos BTL',
                'perfil' => 'Especialista en diseño y montaje de stands para ferias turísticas internacionales, logística de activaciones de marca y eventos en territorio.',
            ],
            [
                'nombre' => 'Cristian Amaya',
                'cargo' => 'Director de Arte Senior',
                'perfil' => 'Especialista en identidad visual, branding territorial, sistemas gráficos de marca ciudad y dirección de arte para campañas masivas.',
            ],
            [
                'nombre' => 'Aura María Jaramillo',
                'cargo' => 'Productora Ejecutiva Audiovisual',
                'perfil' => 'Amplia trayectoria en filmación cinematográfica, comerciales de televisión, bancos de imágenes turísticas y rodajes complejos en territorio.',
            ],
        ],
        'ponderacion_afinidad_ia' => [
            'pesos' => [
                'correspondencia_objeto_y_alcance_lineas' => 35,
                'correspondencia_actividades_y_entregables' => 35,
                'similitud_casos_portafolio' => 20,
                'correspondencia_sectores_y_publicos' => 10,
            ],
            'bandas_calificacion' => [
                'muy_alta' => ['min' => 85, 'max' => 100, 'etiqueta' => 'Afinidad Muy Alta - Prioridad comercial inmediata. Convocar reunión técnica.'],
                'alta' => ['min' => 70, 'max' => 84, 'etiqueta' => 'Afinidad Alta - Oportunidad viable. Evaluar capacidad operativa y plazos.'],
                'media' => ['min' => 50, 'max' => 69, 'etiqueta' => 'Afinidad Media - Requiere consorcio / aliados o tiene componentes mixtos.'],
                'baja' => ['min' => 0, 'max' => 49, 'etiqueta' => 'Afinidad Baja - Descartar del radar comercial activo.'],
            ],
        ],
        'entidades_objetivo' => [
            'FONTUR (Patrimonio Autónomo Fondo Nacional del Turismo)',
            'Ministerio de Comercio, Industria y Turismo (MinCIT)',
            'ProColombia y PROMPERÚ',
            'Instituto Distrital de Turismo de Bogotá (IDT)',
            'Bureaus de Convenciones (Cali-Valle, Medellín, Cartagena, Bogotá)',
            'Secretarías de Turismo, Cultura y Desarrollo Económico departamentales y municipales',
            'Cámaras de Comercio de todo el país',
            'Cajas de Compensación Familiar (Comfandi, Colsubsidio, Compensar, Comfenalco)',
            'Gobernaciones (Valle del Cauca, La Guajira, Atlántico, Antioquia, Cundinamarca, Santander, Quindío, Huila, Bolívar, etc.)',
            'Alcaldías de ciudades capitales e intermedias con vocación turística',
        ],
        'criterios_exclusion' => [
            'Obras civiles, pavimentación, acueductos, alcantarillados, edificaciones o infraestructura física',
            'Vigilancia, seguridad privada, transporte de valores o armamento',
            'Aseo, limpieza, cafetería, desinfección o recolección de residuos',
            'Suministro de medicamentos, insumos médicos, material quirúrgico o reactivos',
            'Alimentación escolar (PAE), comedores comunitarios o víveres perecederos',
            'Compra y suministro de vehículos, maquinaria pesada, combustible o repuestos mecánicos',
            'Interventoría de obras civiles o de infraestructura de transporte',
            'Seguros, pólizas, servicios financieros o bancarios',
            'Servicios funerarios o de disposición final',
            'Suministro de dotaciones de vestuario no relacionadas con eventos o campañas',
        ],
        'rango_presupuesto_ideal' => 'Procesos desde $50.000.000 COP hasta $12.000.000.000 COP en postulación directa, y hasta $30.000.000.000 COP en Consorcio o Unión Temporal.',
        'notas_adicionales' => 'Tornus S.A.S. cuenta con un capital de trabajo RUP de $5.058.026.000 COP a corte 31/12/2025, respaldando holgadamente procesos de alta cuantía con liquidez de 8,17 y endeudamiento de 0,11. Ponderar con máxima afinidad oportunidades en turismo, marca territorial, campañas creativas 360°, producción de contenidos y ferias internacionales.',
    ];

    protected function getProfilePath(): string
    {
        if (app()->environment('testing')) {
            return storage_path('app/tornus_profile_testing.json');
        }
        return storage_path('app/tornus_profile.json');
    }

    public static function getActiveProfile(): array
    {
        $path = app()->environment('testing')
            ? storage_path('app/tornus_profile_testing.json')
            : storage_path('app/tornus_profile.json');

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data) && !empty($data['propuesta_valor']) && ($data['nombre_agencia'] ?? '') !== 'Tornus Agency Test') {
                // Asegurar que si faltan claves avanzadas, se combinen con DEFAULT_PROFILE
                return array_merge(self::DEFAULT_PROFILE, $data);
            }
        }

        return self::DEFAULT_PROFILE;
    }

    public function index()
    {
        $profile = self::getActiveProfile();
        $filtersCO = \App\Services\RadarFilterService::getData('CO');
        $filtersPE = \App\Services\RadarFilterService::getData('PE');
        $currentMarket = \App\Services\MarketService::current();

        return view('perfil-tornus', compact('profile', 'filtersCO', 'filtersPE', 'currentMarket'));
    }

    public function update(Request $request)
    {
        $currentProfile = self::getActiveProfile();

        $updatedData = array_merge($currentProfile, [
            'nombre_agencia' => trim($request->input('nombre_agencia', $currentProfile['nombre_agencia'] ?? 'TORNUS S.A.S.')),
            'marca_comercial' => trim($request->input('marca_comercial', $currentProfile['marca_comercial'] ?? 'Tornus Agency')),
            'sitio_web' => trim($request->input('sitio_web', $currentProfile['sitio_web'] ?? '')),
            'redes_sociales' => [
                'instagram' => trim($request->input('instagram', $currentProfile['redes_sociales']['instagram'] ?? '')),
                'linkedin' => trim($request->input('linkedin', $currentProfile['redes_sociales']['linkedin'] ?? '')),
            ],
            'propuesta_valor' => trim($request->input('propuesta_valor', $currentProfile['propuesta_valor'] ?? '')),
            'posicionamiento_sintetico' => trim($request->input('posicionamiento_sintetico', $currentProfile['posicionamiento_sintetico'] ?? '')),
            'servicios_principales' => array_values(array_filter(array_map('trim', explode("\n", $request->input('servicios_principales', ''))))),
            'entidades_objetivo' => array_values(array_filter(array_map('trim', explode("\n", $request->input('entidades_objetivo', ''))))),
            'criterios_exclusion' => array_values(array_filter(array_map('trim', explode("\n", $request->input('criterios_exclusion', ''))))),
            'rango_presupuesto_ideal' => trim($request->input('rango_presupuesto_ideal', $currentProfile['rango_presupuesto_ideal'] ?? '')),
            'notas_adicionales' => trim($request->input('notas_adicionales', $currentProfile['notas_adicionales'] ?? '')),
        ]);

        @file_put_contents($this->getProfilePath(), json_encode($updatedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back()->with('status', 'Perfil base y parámetros estratégicos de Tornus guardados exitosamente. La base de conocimiento de Gemini ha sido actualizada.');
    }
}
