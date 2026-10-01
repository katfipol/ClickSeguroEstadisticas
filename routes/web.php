<?php

use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [AnalisisController::class, 'dashboard'])->name('dashboard');
    Route::get('/analyze', fn () => redirect()->route('dashboard'));
    // Límite propio para proteger la cuota de la API externa (la gratuita de VirusTotal es de ~4/min).
    Route::post('/analyze', [AnalisisController::class, 'store'])->middleware('throttle:4,1')->name('analisis.store');
    Route::get('/analysis/{id}', [AnalisisController::class, 'show'])->whereNumber('id')->name('analisis.show');
    Route::post('/analysis/{id}/reanalyze', [AnalisisController::class, 'reanalizar'])->whereNumber('id')->middleware('throttle:4,1')->name('analisis.reanalizar');
    Route::delete('/analysis/{id}', [AnalisisController::class, 'destroy'])->whereNumber('id')->name('analisis.destroy');
    Route::get('/history', [AnalisisController::class, 'history'])->name('historial');

    Route::get('/statistics', [EstadisticaController::class, 'index'])->name('estadisticas');

    Route::get('/report', [ReporteController::class, 'create'])->name('reportes.create');
    Route::post('/report', [ReporteController::class, 'store'])->middleware('throttle:10,1')->name('reportes.store');
});
