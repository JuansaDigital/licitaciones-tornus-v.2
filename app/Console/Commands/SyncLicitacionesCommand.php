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
            
            // 1. Carga inicial legacy solo si la base de datos está completamente vacía
            $countExisting = \App\Models\Licitacion::on('colombia')->count();
            $legacyDb = base_path('informacion extra/LICITACIONES TORNUS COLOMBIA/data/licitaciones.db');
            if ($countExisting === 0 && file_exists($legacyDb)) {
                $imported = $secop->importFromLegacyDatabase($legacyDb, 1000);
                $this->info("  Importados {$imported} registros históricos iniciales de Colombia.");
            }

            // 2. Intentar API en vivo con ventana de 5 meses y purga automática
            $live = $secop->syncFromSocrata(100);
            $syncedCount = is_array($live) ? ($live['synced'] ?? 0) : $live;
            $purgedCount = is_array($live) ? ($live['purged'] ?? 0) : 0;
            $this->info("  Sincronizados {$syncedCount} registros en vivo desde SECOP II (ventana 5 meses).");
            $this->info("  Depuradas {$purgedCount} propuestas vencidas (respetando Cartera y evaluaciones IA).");
        }

        if ($market === 'PE' || $market === 'ALL') {
            $this->line("Procesando Perú (SEACE / OECE)...");

            // Consultar API OCDS en vivo (sin archivos estáticos/CSV)
            $live = $oece->syncFromApi(5);
            $purgedPe = $oece->purgeExpired('peru');
            $this->info("  Sincronizados {$live} registros reales desde API OCDS Perú.");
            $this->info("  Depuradas {$purgedPe} propuestas vencidas en Perú.");
        }

        // Despachar alertas automáticas por correo para propuestas detectadas
        $this->line("Verificando alertas por correo para el equipo...");
        $this->call('tornus:send-alerts', ['market' => $market, '--hours' => 2]);

        $this->info("Sincronización completada exitosamente.");
        return 0;
    }
}

