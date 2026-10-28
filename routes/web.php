<?php

use App\Http\Controllers\AfinidadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CarteraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\RadarController;
use App\Services\MarketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirección inicial
Route::get('/', function () {
    if (Auth::check()) {
        if (!MarketService::hasSelected()) {
            return redirect()->route('market.select');
        }
        return redirect()->route('radar');
    }
    return redirect()->route('login');
});

// Autenticación independiente (Login Aparte)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Feed de Calendario iCal (.ics) - Suscripción Universal
Route::get('/calendario/feed/{market?}', [CalendarController::class, 'feed'])->name('calendar.feed');

// Rutas Protegidas del Panel
Route::middleware('auth')->group(function () {
    // Pantalla de Selección de País / Mercado dentro del Panel
    Route::get('/seleccionar-pais', [MarketController::class, 'showSelect'])->name('market.select');
    Route::get('/seleccionar-pais/{market}', [MarketController::class, 'selectMarket'])->name('market.set');
    Route::get('/market/switch/{market}', [MarketController::class, 'selectMarket'])->name('market.switch');

    // Módulo de Configuración de Usuarios y Roles
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');
    Route::post('/configuracion/usuarios', [ConfiguracionController::class, 'store'])->name('configuracion.usuarios.store');
    Route::post('/configuracion/usuarios/{id}/password', [ConfiguracionController::class, 'updatePassword'])->name('configuracion.usuarios.password');
    Route::post('/configuracion/usuarios/{id}/toggle-status', [ConfiguracionController::class, 'toggleStatus'])->name('configuracion.usuarios.toggle_status');
    Route::delete('/configuracion/usuarios/{id}', [ConfiguracionController::class, 'destroy'])->name('configuracion.usuarios.destroy');

    // Módulo A & B: Radar en Vivo y Filtros
    Route::get('/radar', [RadarController::class, 'index'])->name('radar');
    Route::post('/radar/sync', [RadarController::class, 'sync'])->name('radar.sync');
    Route::post('/radar/{id}/cartera', [RadarController::class, 'toggleCartera'])->name('radar.cartera');
    Route::post('/radar/keywords', [RadarController::class, 'addKeyword'])->name('radar.keywords.add');
    Route::post('/radar/keywords/update', [RadarController::class, 'updateKeyword'])->name('radar.keywords.update');
    Route::post('/radar/keywords/remove', [RadarController::class, 'removeKeyword'])->name('radar.keywords.remove');
    Route::post('/radar/exclusions', [RadarController::class, 'addExclusion'])->name('radar.exclusions.add');
    Route::post('/radar/exclusions/remove', [RadarController::class, 'removeExclusion'])->name('radar.exclusions.remove');
    Route::post('/radar/filters/reset', [RadarController::class, 'resetFilters'])->name('radar.filters.reset');

    // Módulo E: Mi Cartera (Tablero Comercial)
    Route::get('/cartera', [CarteraController::class, 'index'])->name('cartera');
    Route::post('/cartera/{id}/status', [CarteraController::class, 'updateStatus'])->name('cartera.status');
    Route::get('/cartera/export', [CarteraController::class, 'exportCsv'])->name('cartera.export');

    // Módulo D: Evaluador de Afinidad IA (Gemini)
    Route::get('/afinidad', [AfinidadController::class, 'index'])->name('afinidad');
    Route::post('/afinidad/{id}/evaluar', [AfinidadController::class, 'evaluate'])->name('afinidad.evaluate');
    Route::post('/afinidad/batch', [AfinidadController::class, 'evaluateBatch'])->name('afinidad.batch');
});
