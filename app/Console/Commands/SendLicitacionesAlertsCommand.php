<?php

namespace App\Console\Commands;

use App\Models\Licitacion;
use App\Services\AlertNotificationService;
use Illuminate\Console\Command;

class SendLicitacionesAlertsCommand extends Command
{
    protected $signature = 'tornus:send-alerts {market? : CO, PE o ALL (ALL por defecto)} {--hours=48 : Ventana de horas hacia atrás para nuevas propuestas} {--min-days=3 : Días mínimos restantes para el cierre (excluye cierres inminentes)}';
    protected $description = 'Envía alertas por correo de nuevas oportunidades detectadas (con ventana amplia de ejecución) a los miembros suscritos';

    public function handle(): int
    {
        $market = strtoupper($this->argument('market') ?? 'ALL');
        $hours = (int) ($this->option('hours') ?: 48);
        $minDays = (int) ($this->option('min-days') ?: 3);

        $this->info("Buscando propuestas recién convocadas en las últimas {$hours}h con ventana amplia (>= {$minDays} días de cierre)...");

        $marketsToProcess = [];
        if ($market === 'CO' || $market === 'ALL') {
            $marketsToProcess[] = 'CO';
        }
        if ($market === 'PE' || $market === 'ALL') {
            $marketsToProcess[] = 'PE';
        }

        $totalSent = 0;

        foreach ($marketsToProcess as $mCode) {
            $licitaciones = AlertNotificationService::getEligibleNewTendersForAlerts($mCode, $hours, $minDays);

            $count = count($licitaciones);
            $this->line("Mercado {$mCode}: {$count} propuestas recién convocadas con ventana amplia encontradas.");

            if ($count > 0) {
                $sent = AlertNotificationService::sendNewTendersAlert($licitaciones, $mCode);
                $totalSent += $sent;
                $this->info("  Alertas enviadas a {$sent} destinatarios en mercado {$mCode}.");
            }
        }

        $this->info("Proceso de alertas finalizado. Total correos despachados: {$totalSent}.");
        return 0;
    }
}

