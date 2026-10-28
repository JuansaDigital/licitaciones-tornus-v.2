<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerfilTornusController extends Controller
{
    protected function getProfilePath(): string
    {
        return storage_path('app/tornus_profile.json');
    }

    public function index()
    {
        $path = $this->getProfilePath();
        $profile = [];
        if (file_exists($path)) {
            $profile = json_decode(file_get_contents($path), true) ?: [];
        }

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

