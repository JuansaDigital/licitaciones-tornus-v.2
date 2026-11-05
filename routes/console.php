<?php

use Illuminate\Support\Facades\Schedule;

// Tarea programada (Cron Job) para sincronización continua de compras públicas
Schedule::command('tornus:sync')
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground();

// Tarea programada para despachar alertas diarias de nuevas oportunidades (ventana amplia >= 3 días)
Schedule::command('tornus:send-alerts ALL --hours=24 --min-days=3')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();
