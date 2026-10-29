<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerfilTornusController extends Controller
{
    public const DEFAULT_PROFILE = [
        'nombre_agencia' => 'Tornus Agency',
        'sitio_web' => 'https://tornus.agency',
        'redes_sociales' => [
            'instagram' => 'https://instagram.com/tornusagency',
            'linkedin' => 'https://linkedin.com/company/tornusagency',
        ],
        'propuesta_valor' => 'Agencia especializada en marketing turístico, promoción de destinos, branding territorial, producción audiovisual de alto impacto, estrategia digital, famtrips, ruedas de negocios y operación logística de eventos promocionales e institucionales en Colombia y Latinoamérica.',
        'servicios_principales' => [
            'Marketing turístico y promoción de destinos nacionales e internacionales',
            'Diseño e implementación de marca ciudad, marca región y branding territorial',
            'Estrategia digital, campañas de pauta publicitaria y posicionamiento SEO/SEM',
            'Producción audiovisual profesional, fotografía turística y banco de contenidos',
            'Diseño y desarrollo de portales web turísticos, plataformas interactivas y apps',
            'Organización de FamTrips, PressTrips, ruedas de negocios y vitrinas turísticas',
            'Operación logística integral de eventos culturales, gastronómicos, ferias y congresos',
            'Consultoría en planificación estratégica de turismo sostenible y estudios de mercado',
        ],
        'entidades_objetivo' => [
            'FONTUR (Patrimonio Autónomo Fondo Nacional del Turismo)',
            'Ministerio de Comercio, Industria y Turismo (MinCIT)',
            'ProColombia y PROMPERÚ',
            'Instituto Distrital de Turismo (IDT) y Bureaus de Convenciones',
            'Secretarías de Turismo, Cultura y Desarrollo Económico (Gobernaciones y Alcaldías)',
            'Cámaras de Comercio y Cajas de Compensación Familiar',
        ],
        'criterios_exclusion' => [
            'Obras civiles, pavimentación, mantenimiento vial y construcción de infraestructura física',
            'Vigilancia y seguridad privada, armamento o dotaciones policiales/militares',
            'Suministro de medicamentos, insumos hospitalarios y material quirúrgico',
            'Suministro de combustible, lubricantes, repuestos y mantenimiento de parque automotor',
            'Servicios de aseo, cafetería, fumigación y recolección de residuos sólidos',
            'Alimentación escolar (PAE) y raciones militares',
        ],
        'rango_presupuesto_ideal' => 'Proyectos estratégicos desde $40,000,000 COP hasta $3,500,000,000 COP (Postulación directa o en Consorcio / Unión Temporal según cuantía).',
        'notas_adicionales' => 'Priorizar procesos donde se valore la creatividad estratégica, producción de contenidos y promoción turística integral.',
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
                return $data;
            }
        }

        return self::DEFAULT_PROFILE;
    }

    public function index()
    {
        $profile = self::getActiveProfile();

        return view('perfil-tornus', compact('profile'));
    }

    public function update(Request $request)
    {
        $data = [
            'nombre_agencia' => trim($request->input('nombre_agencia', 'Tornus Agency')),
            'sitio_web' => trim($request->input('sitio_web', '')),
            'redes_sociales' => [
                'instagram' => trim($request->input('instagram', '')),
                'linkedin' => trim($request->input('linkedin', '')),
            ],
            'propuesta_valor' => trim($request->input('propuesta_valor', '')),
            'servicios_principales' => array_values(array_filter(array_map('trim', explode("\n", $request->input('servicios_principales', ''))))),
            'entidades_objetivo' => array_values(array_filter(array_map('trim', explode("\n", $request->input('entidades_objetivo', ''))))),
            'criterios_exclusion' => array_values(array_filter(array_map('trim', explode("\n", $request->input('criterios_exclusion', ''))))),
            'rango_presupuesto_ideal' => trim($request->input('rango_presupuesto_ideal', '')),
            'notas_adicionales' => trim($request->input('notas_adicionales', '')),
        ];

        @file_put_contents($this->getProfilePath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back()->with('status', 'Perfil base y criterios de Tornus Agency guardados exitosamente. Las futuras evaluaciones de Gemini se contrastarán con estos datos.');
    }
}

