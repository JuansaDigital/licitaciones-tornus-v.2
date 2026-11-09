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
        $nowDateTime = now()->format('Y-m-d H:i:s');
        $today = now()->toDateString();

        // REGLA DE VIGENCIA Y AFINIDAD (Nuevo Modelo): Oportunidades viables desde 35% en adelante (Niveles A, B y C)
        // que estén vigentes (abiertas) o guardadas en Cartera.
        $filterNivel = strtoupper(trim((string)$request->input('nivel', 'all')));

        $baseActiveQuery = function ($includeDiscarded = false) use ($connection, $nowDateTime, $today) {
            $query = Licitacion::on($connection)
                ->whereNotNull('score');

            if (!$includeDiscarded) {
                $query->where('score', '>=', 35);
            }

            return $query->where(function ($q) use ($nowDateTime, $today) {
                $q->where('en_cartera', true)
                  ->orWhere(function ($sub) use ($nowDateTime, $today) {
                      $sub->where(function ($dateQ) use ($nowDateTime, $today) {
                          $dateQ->whereNull('fecha_cierre')
                                ->orWhereRaw("length(fecha_cierre) > 10 AND fecha_cierre >= ?", [$nowDateTime])
                                ->orWhereRaw("length(fecha_cierre) <= 10 AND substr(fecha_cierre, 1, 10) >= ?", [$today]);
                      })->whereNotIn('estado', \App\Models\Licitacion::NON_ACTIVE_STATES);
                  });
            });
        };

        $includeDiscarded = ($filterNivel === 'D' || $filterNivel === 'DESCARTAR');
        $query = $baseActiveQuery($includeDiscarded);

        // Filtro por Nivel de Afinidad (Rúbrica Oficial: A >= 75, B 55-74, C 35-54, D < 35)
        if ($filterNivel === 'A' || $filterNivel === 'ALTA') {
            $query->where('score', '>=', 75);
        } elseif ($filterNivel === 'B' || $filterNivel === 'MEDIA') {
            $query->whereBetween('score', [55, 74]);
        } elseif ($filterNivel === 'C' || $filterNivel === 'BAJA') {
            $query->whereBetween('score', [35, 54]);
        } elseif ($filterNivel === 'D' || $filterNivel === 'DESCARTAR') {
            $query->where('score', '<', 35);
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
            'total_evaluadas' => $baseActiveQuery(false)->count(),
            'nivel_a' => (clone $baseActiveQuery(true))->where('score', '>=', 75)->count(),
            'nivel_b' => (clone $baseActiveQuery(true))->whereBetween('score', [55, 74])->count(),
            'nivel_c' => (clone $baseActiveQuery(true))->whereBetween('score', [35, 54])->count(),
            'nivel_d' => (clone $baseActiveQuery(true))->where('score', '<', 35)->count(),
            // Claves de compatibilidad
            'alta' => (clone $baseActiveQuery(true))->where('score', '>=', 75)->count(),
            'media' => (clone $baseActiveQuery(true))->whereBetween('score', [55, 74])->count(),
            'baja' => (clone $baseActiveQuery(true))->whereBetween('score', [35, 54])->count(),
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

