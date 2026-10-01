<?php

return [
    'api_key'  => env('VIRUSTOTAL_API_KEY'),
    'base_url' => 'https://www.virustotal.com/api/v3',
    'timeout'  => 15,   // segundos por solicitud HTTP
    'max_polls' => 6,   // intentos de consulta del resultado (3 s entre cada uno)
    // Clasificación: >= umbral_peligroso motores maliciosos => Peligroso;
    // al menos 1 malicioso o sospechoso => Sospechoso; en otro caso => Seguro.
    'umbral_peligroso' => 3,
];
