<?php

namespace App\Services\Export;

use App\Services\MarketService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportService
{
    /**
     * Generar y descargar el reporte ejecutivo Excel multihash de 3 hojas
     */
    public static function download(string $type = 'radar'): BinaryFileResponse
    {
        $market = MarketService::current();
        $isPeru = MarketService::isPeru();
        
        $dbPath = $isPeru 
            ? database_path('licitaciones_pe.sqlite') 
            : database_path('licitaciones_co.sqlite');
            
        $filtersPath = storage_path("app/radar_filters_" . strtolower($market) . ".json");
        $logoPath = public_path('assets/logo-tornus.png');
        
        $timestamp = date('Ymd_His');
        $filename = "Reporte_Ejecutivo_Tornus_{$market}_{$timestamp}.xlsx";
        $outputPath = storage_path("app/{$filename}");

        $params = json_encode([
            'market' => $market,
            'type' => $type,
            'source' => MarketService::sourceName()
        ]);

        $scriptPath = base_path('scripts/export_multisheet_excel.py');
        $pythonBinary = 'python';

        $cmd = sprintf(
            '%s %s %s %s %s %s %s',
            escapeshellcmd($pythonBinary),
            escapeshellarg($scriptPath),
            escapeshellarg($dbPath),
            escapeshellarg($logoPath),
            escapeshellarg($outputPath),
            escapeshellarg($params),
            escapeshellarg($filtersPath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new \RuntimeException("No se pudo generar el reporte Excel: " . implode(" ", $output));
        }

        return response()->download($outputPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}

