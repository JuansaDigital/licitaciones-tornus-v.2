<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\Ingestion\OecePeruService;
use App\Services\Ingestion\SecopColombiaService;
use App\Services\MarketService;
use Illuminate\Http\Request;

class RadarController extends Controller
{
    public function index(Request $request)
    {
        $connection = MarketService::connection();
        $query = Licitacion::on($connection);

        // 1. Filtro de búsqueda textual con Anti-Falsos Positivos
        $search = trim($request->input('q', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('objeto', 'like', "%{$search}%")
                  ->orWhere('entidad', 'like', "%{$search}%")
                  ->orWhere('referencia', 'like', "%{$search}%");
            });

            // Regla anti-falso positivo si busca "marca"
            if (stripos($search, 'marca') !== false) {
                $query->where('objeto', 'not like', '%Cundinamarca%');
            }
            // Regla anti-falso positivo si busca "campaña"
            if (stripos($search, 'campaña') !== false) {
                $query->where('objeto', 'not like', '%militar%')
                      ->where('objeto', 'not like', '%vacunación%');
            }
        }

        // 2. Filtro territorial
        $selectedRegion = trim($request->input('region', ''));
        if (!empty($selectedRegion)) {
            $query->where('departamento', 'like', "%{$selectedRegion}%");
        }

        // 3. Regiones disponibles para el filtro
        $regions = Licitacion::on($connection)
            ->whereNotNull('departamento')
            ->where('departamento', '!=', '')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento')
            ->toArray();

        // 4. Conteo de totales
        $totalCount = Licitacion::on($connection)->count();

        // 5. Filtro de Urgencia / Vencimiento
        $selectedUrgency = $request->input('urgency', 'all');
        $now = now();

        if ($selectedUrgency === 'critical') {
            // < 48 horas
            $query->whereNotNull('fecha_cierre')
                  ->where('fecha_cierre', '>=', $now->toIso8601String())
                  ->where('fecha_cierre', '<=', $now->copy()->addHours(48)->toIso8601String());
        } elseif ($selectedUrgency === 'week') {
            // 3 a 7 días
            $query->whereNotNull('fecha_cierre')
                  ->where('fecha_cierre', '>', $now->copy()->addHours(48)->toIso8601String())
                  ->where('fecha_cierre', '<=', $now->copy()->addDays(7)->toIso8601String());
        } elseif ($selectedUrgency === 'ideal') {
            // > 7 días
            $query->whereNotNull('fecha_cierre')
                  ->where('fecha_cierre', '>', $now->copy()->addDays(7)->toIso8601String());
        } elseif ($selectedUrgency === 'recent') {
            // Convocada en las últimas 24 horas
            $query->whereNotNull('fecha_publicacion')
                  ->where('fecha_publicacion', '>=', $now->copy()->subHours(24)->toIso8601String());
        }

        $licitaciones = $query
            ->orderByRaw("CASE WHEN fecha_cierre IS NOT NULL AND fecha_cierre != '' THEN fecha_cierre ELSE '9999-12-31' END ASC")
            ->paginate(15)
            ->withQueryString();

        return view('radar', compact(
            'licitaciones',
            'regions',
            'selectedRegion',
            'selectedUrgency',
            'search',
            'totalCount'
        ));
    }

    public function toggleCartera(Request $request, $id)
    {
        $connection = MarketService::connection();
        $licitacion = Licitacion::on($connection)->findOrFail($id);

        $licitacion->en_cartera = !$licitacion->en_cartera;
        if ($licitacion->en_cartera && empty($licitacion->cartera_estado)) {
            $licitacion->cartera_estado = 'Identificada';
        }
        $licitacion->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'en_cartera' => $licitacion->en_cartera,
                'cartera_estado' => $licitacion->cartera_estado
            ]);
        }

        return back()->with('status', $licitacion->en_cartera ? 'Licitación agregada a Mi Cartera.' : 'Licitación retirada de Cartera.');
    }

    public function sync(Request $request, SecopColombiaService $secop, OecePeruService $oece)
    {
        $isPeru = MarketService::isPeru();
        $synced = 0;

        if ($isPeru) {
            $synced = $oece->syncFromApi(2);
        } else {
            $synced = $secop->syncFromSocrata(50);
        }

        return back()->with('status', "Radar sincronizado. Se actualizaron {$synced} registros en vivo.");
    }
}

