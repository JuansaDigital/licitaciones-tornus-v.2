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

// Tarea programada para despachar alertas diarias de nuevas oportunidades (ventana amplia >= 3 días)
Schedule::command('tornus:send-alerts ALL --hours=24 --min-days=3')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();
