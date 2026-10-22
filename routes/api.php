<?php

use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Análisis de URLs
        Route::get('/analyses', [AnalisisController::class, 'index']);
        Route::post('/analyses', [AnalisisController::class, 'store'])->middleware('throttle:20,1');
        Route::get('/analyses/{id}', [AnalisisController::class, 'show'])->whereNumber('id');
        Route::get('/analyses/{id}/status', [AnalisisController::class, 'status'])->whereNumber('id');
        Route::post('/analyses/{id}/reanalyze', [AnalisisController::class, 'reanalyze'])->whereNumber('id')->middleware('throttle:20,1');
        Route::delete('/analyses/{id}', [AnalisisController::class, 'destroy'])->whereNumber('id');
        Route::get('/analyses/{id}/report', [InformeController::class, 'analisis'])->whereNumber('id');

        // Archivos
        Route::get('/files', [ArchivoController::class, 'index']);
        Route::post('/files', [ArchivoController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/files/{id}', [ArchivoController::class, 'show'])->whereNumber('id');
        Route::delete('/files/{id}', [ArchivoController::class, 'destroy'])->whereNumber('id');
        Route::get('/files/{id}/report', [InformeController::class, 'archivo'])->whereNumber('id');

        // Dominios
        Route::get('/domains/lookup', [FichaController::class, 'show'])->middleware('throttle:10,1');

        // Documentos
        Route::get('/documents', [DocumentoController::class, 'index']);
        Route::post('/documents', [DocumentoController::class, 'store'])->middleware('throttle:6,1');
        Route::get('/documents/{id}', [DocumentoController::class, 'show'])->whereNumber('id');
        Route::get('/documents/{id}/status', [DocumentoController::class, 'status'])->whereNumber('id');

        // Estadísticas y Reportes
        Route::get('/statistics/personal', [EstadisticaController::class, 'personal']);
        Route::get('/statistics/global', [EstadisticaController::class, 'global']);
        Route::post('/reports', [ReporteController::class, 'store'])->middleware('throttle:10,1');
    });
});