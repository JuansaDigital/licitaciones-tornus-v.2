<?php

use App\Services\SyncConfigService;
use Illuminate\Support\Facades\Schedule;

// Tarea programada (Cron Job) con frecuencia configurable desde el panel de Configuración
$syncFreq = SyncConfigService::getFrequency();
$syncCommand = Schedule::command('tornus:sync')->withoutOverlapping()->runInBackground();

match ($syncFreq) {
    'hourly' => $syncCommand->hourly(),
    'everyFourHours' => $syncCommand->everyFourHours(),
    'everySixHours' => $syncCommand->everySixHours(),
    'twiceDaily' => $syncCommand->twiceDaily(8, 17),
    'daily' => $syncCommand->dailyAt('06:00'),
    default => $syncCommand->everyTwoHours(),
};

// Tarea programada para despachar alertas por correo según configuración del panel
$emailFreq = SyncConfigService::getEmailFrequency();

if ($emailFreq === 'daily') {
    Schedule::command('tornus:send-alerts ALL --hours=24 --min-days=3')
        ->dailyAt('08:00')
        ->withoutOverlapping()
        ->runInBackground();
} elseif ($emailFreq === 'twiceDaily') {
    Schedule::command('tornus:send-alerts ALL --hours=12 --min-days=3')
        ->twiceDaily(8, 17)
        ->withoutOverlapping()
        ->runInBackground();
}
// Si es 'realtime', se despachan alertas en cada ciclo de tornus:sync.
// Si es 'disabled', no se ejecutan alertas por correo.
