<?php

namespace App\Console\Commands;

use App\Models\Licitacion;
use App\Services\GeminiAffinityService;
use App\Services\MarketService;
use Illuminate\Console\Command;

class AutoTriageLicitacionesCommand extends Command
{
    protected $signature = 'tornus:auto-triage {market? : CO, PE o ALL (por defecto ALL)} {--limit=20 : Cantidad máxima de ofertas a evaluar por mercado}';
    protected $description = 'Clasifica automáticamente con IA (Gemini) las licitaciones pendientes y descarta las no afines a Tornus';

    public function handle(GeminiAffinityService $gemini): int
    {
        $marketArg = strtoupper($this->argument('market') ?? 'ALL');
        $limit = (int) $this->option('limit');

        $markets = ($marketArg === 'ALL') ? ['CO', 'PE'] : [$marketArg];

        $totalEvaluadas = 0;
        $totalDescartadas = 0;
        $totalViables = 0;

        foreach ($markets as $mkt) {
            $connection = ($mkt === 'PE') ? 'peru' : 'colombia';
            $mktName = ($mkt === 'PE') ? 'Perú' : 'Colombia';

            $this->line("Procesando Auto-Triage para {$mktName}...");

            $pendientes = Licitacion::on($connection)
                ->whereNull('score')
                ->whereNotIn('estado', ['Cancelado', 'Cerrado', 'Desierto', 'Adjudicado'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            if ($pendientes->isEmpty()) {
                $this->info("  No hay propuestas pendientes de evaluar en {$mktName}.");
                continue;
            }

            $this->info("  Encontradas {$pendientes->count()} propuestas para evaluar con IA en {$mktName}...");

            $bar = $this->output->createProgressBar($pendientes->count());
            $bar->start();

            foreach ($pendientes as $lic) {
                try {
                    $result = $gemini->evaluate($lic);
                    $totalEvaluadas++;

                    $score = $result['score'] ?? 0;
                    $estrategia = $result['estrategia_postulacion'] ?? '';

                    if ($score < 40 || strcasecmp($estrategia, 'Descartar') === 0) {
                        $totalDescartadas++;
                    } else {
                        $totalViables++;
                    }
                } catch (\Throwable $e) {
                    $this->error("\n  Error evaluando proceso #{$lic->id}: " . $e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info("Resumen de Auto-Triage con IA:");
        $this->line("  Total Evaluadas: {$totalEvaluadas}");
        $this->line("  Viables (ALTA / MEDIA): {$totalViables}");
        $this->line("  Descartadas automáticamente por IA: {$totalDescartadas}");

        return 0;
    }
}

