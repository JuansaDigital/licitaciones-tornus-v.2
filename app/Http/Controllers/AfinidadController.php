<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\GeminiAffinityService;
use App\Services\MarketService;
use Illuminate\Http\Request;

class AfinidadController extends Controller
{
    public function index(Request $request)
    {
        $connection = MarketService::connection();
        $query = Licitacion::on($connection);

        $filterNivel = $request->input('nivel', 'all');
        if (in_array(strtoupper($filterNivel), ['ALTA', 'MEDIA', 'BAJA'])) {
            $query->where('nivel', strtoupper($filterNivel));
        }

        $licitaciones = $query
            ->whereNotNull('score')
            ->orderBy('score', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_evaluadas' => Licitacion::on($connection)->whereNotNull('score')->count(),
            'alta' => Licitacion::on($connection)->where('nivel', 'ALTA')->count(),
            'media' => Licitacion::on($connection)->where('nivel', 'MEDIA')->count(),
            'baja' => Licitacion::on($connection)->where('nivel', 'BAJA')->count(),
        ];

        return view('afinidad', compact('licitaciones', 'stats', 'filterNivel'));
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
}

