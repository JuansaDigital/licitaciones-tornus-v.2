<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\GeminiAffinityService;
use App\Services\MarketService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AfinidadController extends Controller
{
    public function index(Request $request)
    {
        $connection = MarketService::connection();
        $query = Licitacion::on($connection)->whereNotNull('score');

        // Filtro por Nivel de Afinidad (ALTA, MEDIA, BAJA)
        $filterNivel = $request->input('nivel', 'all');
        if (in_array(strtoupper($filterNivel), ['ALTA', 'MEDIA', 'BAJA'])) {
            $query->where('nivel', strtoupper($filterNivel));
        }

        // Filtro por Fecha (Período o Rango Personalizado)
        $filterPeriodo = $request->input('periodo', 'all');
        $fechaDesde = $request->input('desde');
        $fechaHasta = $request->input('hasta');

        if (!empty($fechaDesde)) {
            $query->whereDate('evaluado_en', '>=', $fechaDesde);
        } elseif ($filterPeriodo === 'today') {
            $query->whereDate('evaluado_en', Carbon::today()->toDateString());
        } elseif ($filterPeriodo === '7d') {
            $query->where('evaluado_en', '>=', Carbon::now()->subDays(7));
        } elseif ($filterPeriodo === '30d') {
            $query->where('evaluado_en', '>=', Carbon::now()->subDays(30));
        } elseif ($filterPeriodo === '5m') {
            $query->where('evaluado_en', '>=', Carbon::now()->subMonths(5));
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('evaluado_en', '<=', $fechaHasta);
        }

        $licitaciones = $query
            ->orderBy('score', 'desc')
            ->orderBy('evaluado_en', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_evaluadas' => Licitacion::on($connection)->whereNotNull('score')->count(),
            'alta' => Licitacion::on($connection)->where('nivel', 'ALTA')->count(),
            'media' => Licitacion::on($connection)->where('nivel', 'MEDIA')->count(),
            'baja' => Licitacion::on($connection)->where('nivel', 'BAJA')->count(),
        ];

        return view('afinidad', compact(
            'licitaciones',
            'stats',
            'filterNivel',
            'filterPeriodo',
            'fechaDesde',
            'fechaHasta'
        ));
    }

    public function evaluate(Request $request, $id, GeminiAffinityService $gemini)
    {
        $connection = MarketService::connection();
        $licitacion = Licitacion::on($connection)->findOrFail($id);

        $result = $gemini->evaluate($licitacion);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        }

        return back()->with('status', "Licitación evaluada con éxito: Afinidad {$result['nivel']} ({$result['score']}/100)");
    }

    public function evaluateBatch(Request $request, GeminiAffinityService $gemini)
    {
        $connection = MarketService::connection();
        $pendientes = Licitacion::on($connection)
            ->whereNull('score')
            ->limit(10)
            ->get();

        $count = 0;
        foreach ($pendientes as $lic) {
            $gemini->evaluate($lic);
            $count++;
        }

        return back()->with('status', "Se evaluaron exitosamente {$count} licitaciones con el motor de IA.");
    }

    /**
     * Eliminar una propuesta analizada individualmente
     */
    public function destroy(Request $request, $id)
    {
        $connection = MarketService::connection();
        $lic = Licitacion::on($connection)->findOrFail($id);

        if ($lic->en_cartera) {
            $lic->update([
                'score' => null,
                'nivel' => null,
                'justificacion' => null,
                'servicios_sugeridos' => null,
                'estrategia_postulacion' => null,
                'puntos_clave' => null,
                'evaluado_en' => null,
            ]);
            return back()->with('status', "Evaluación IA removida de la propuesta (permanece en Mi Cartera).");
        }

        $lic->delete();
        return back()->with('status', "Propuesta analizada eliminada del sistema.");
    }

    /**
     * Eliminación por selección conjunta (Bulk delete)
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $ids = $request->input('ids');
        $connection = MarketService::connection();

        $lics = Licitacion::on($connection)->whereIn('id', $ids)->get();
        $deleted = 0;
        $reset = 0;

        foreach ($lics as $lic) {
            if ($lic->en_cartera) {
                $lic->update([
                    'score' => null,
                    'nivel' => null,
                    'justificacion' => null,
                    'servicios_sugeridos' => null,
                    'estrategia_postulacion' => null,
                    'puntos_clave' => null,
                    'evaluado_en' => null,
                ]);
                $reset++;
            } else {
                $lic->delete();
                $deleted++;
            }
        }

        $total = $deleted + $reset;
        return back()->with('status', "Se eliminaron {$total} propuestas analizadas con IA ({$deleted} eliminadas definitivamente, {$reset} removidas de IA conservadas en Cartera).");
    }
}

