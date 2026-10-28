<?php

use Illuminate\Support\Facades\Schedule;

// Tarea programada (Cron Job) para sincronización continua de compras públicas
Schedule::command('tornus:sync')
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground();
