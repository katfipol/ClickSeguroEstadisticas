<?php

use Illuminate\Support\Facades\Route;

// En una API pura, web.php no sirve vistas HTML.
// Dejamos únicamente una respuesta JSON de estado en la raíz por seguridad.
Route::get('/', function () {
    return response()->json([
        'sistema' => 'Click Seguro API',
        'estado'  => 'Operativo',
        'version' => 'v1',
    ]);
});