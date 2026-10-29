<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\MarketService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CarteraController extends Controller
{
    const ESTADOS = [
        'Identificada',
        'En Preparación',
        'Presentada',
        'Adjudicada',
        'Descartada'
    ];

    public function index(Request $request)
    {
        $connection = MarketService::connection();
        $licitaciones = Licitacion::on($connection)
            ->where('en_cartera', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Garantizar que toda licitación en cartera tenga un estado válido del Kanban
        foreach ($licitaciones as $lic) {
            if (empty($lic->cartera_estado) || !in_array($lic->cartera_estado, self::ESTADOS)) {
                $lic->cartera_estado = 'Identificada';
                $lic->save();
            }
        }

        // Agrupar por columna del tablero Kanban
        $kanban = [];
        foreach (self::ESTADOS as $estado) {
            $kanban[$estado] = $licitaciones->where('cartera_estado', $estado)->values();
        }

        $totalEnCartera = $licitaciones->count();
        $valorTotal = $licitaciones->sum('cuantia');

        return view('cartera', compact('kanban', 'totalEnCartera', 'valorTotal'));
    }

    public function updateStatus(Request $request, $id)
    {
        $connection = MarketService::connection();
        $licitacion = Licitacion::on($connection)->findOrFail($id);

        $request->validate([
            'cartera_estado' => 'nullable|string|in:' . implode(',', self::ESTADOS),
            'notas' => 'nullable|string'
        ]);

        if ($request->has('cartera_estado')) {
            $licitacion->cartera_estado = $request->input('cartera_estado');
        }

        if ($request->has('notas')) {
            $licitacion->notas = $request->input('notas');
        }

        $licitacion->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Estado de cartera actualizado.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $connection = MarketService::connection();
        $market = MarketService::marketName();
        $items = Licitacion::on($connection)
            ->where('en_cartera', true)
            ->orderBy('cartera_estado')
            ->get();

        $filename = "Cartera_Tornus_{$market}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');
            // Agregar BOM UTF-8 para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID / Referencia',
                'Estado Cartera',
                'Entidad',
                'Departamento',
                'Objeto',
                'Presupuesto',
                'Moneda',
                'Fecha Cierre',
                'Afinidad IA',
                'Puntaje',
                'Notas Internas'
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->referencia ?: $item->external_id,
                    $item->cartera_estado,
                    $item->entidad,
                    $item->departamento,
                    $item->objeto,
                    $item->cuantia,
                    $item->moneda,
                    $item->fecha_cierre,
                    $item->nivel,
                    $item->score,
                    $item->notas
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

