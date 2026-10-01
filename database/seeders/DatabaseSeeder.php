<?php

namespace Database\Seeders;

use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            'Seguro' => 'Ningún motor de seguridad marcó esta URL como riesgosa.',
            'Sospechoso' => 'Pocos motores la marcaron. Evita ingresar datos personales y verifica el origen.',
            'Peligroso' => 'Varios motores la marcaron como maliciosa. No la abras.',
        ];

        foreach ($niveles as $nombre => $descripcion) {
            TipoRiesgo::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }

        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);
    }
}