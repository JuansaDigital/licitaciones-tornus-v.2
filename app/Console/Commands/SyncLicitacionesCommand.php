<?php

namespace App\Console\Commands;

use App\Services\Ingestion\OecePeruService;
use App\Services\Ingestion\SecopColombiaService;
use Illuminate\Console\Command;

class SyncLicitacionesCommand extends Command
{
    protected $signature = 'tornus:sync {market? : CO o PE (o all por defecto)}';
    protected $description = 'Sincroniza e ingesta licitaciones para Colombia y Perú en sus bases de datos aisladas';

    public function handle(SecopColombiaService $secop, OecePeruService $oece): int
    {
        $market = strtoupper($this->argument('market') ?? 'ALL');

        $this->info("Iniciando sincronización para mercado: {$market}");

        if ($market === 'CO' || $market === 'ALL') {
            $this->line("Procesando Colombia (SECOP II)...");
            
            // 1. Primero intentar importar base local legacy si existe
            $legacyDb = base_path('informacion extra/LICITACIONES TORNUS COLOMBIA/data/licitaciones.db');
            if (file_exists($legacyDb)) {
                $imported = $secop->importFromLegacyDatabase($legacyDb, 1000);
                $this->info("  Importados {$imported} registros históricos de Colombia.");
            }

            // 2. Intentar API en vivo
            $live = $secop->syncFromSocrata(50);
            $this->info("  Sincronizados {$live} registros en vivo desde SECOP II Socrata.");
        }

        if ($market === 'PE' || $market === 'ALL') {
            $this->line("Procesando Perú (SEACE / OECE)...");
            
            // 1. Importar CSV local si existe
            $csv = base_path('informacion extra/Tornus_Licitaciones_Peru.csv');
            if (file_exists($csv)) {
                $importedCsv = $oece->importFromCsv($csv);
                $this->info("  Importados {$importedCsv} registros desde CSV de Perú.");
            }

            // 2. Consultar API en vivo
            $live = $oece->syncFromApi(3);
            $this->info("  Sincronizados {$live} registros desde API OCDS Perú.");
        }

        $this->info("Sincronización completada exitosamente.");
        return 0;
    }
}

