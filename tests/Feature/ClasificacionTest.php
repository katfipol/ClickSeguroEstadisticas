<?php

namespace Tests\Feature;

use App\Services\AnalisisService;
use Tests\TestCase;

class ClasificacionTest extends TestCase
{
    /** CP-11: tabla de decisión de la clasificación por umbral (umbral_peligroso = 3). */
    public function test_clasifica_segun_umbral(): void
    {
        $s = app(AnalisisService::class);
        $casos = [
            [0, 0, 'Seguro'], [1, 0, 'Sospechoso'], [0, 1, 'Sospechoso'],
            [2, 0, 'Sospechoso'], [3, 0, 'Peligroso'], [10, 0, 'Peligroso'],
        ];
        foreach ($casos as [$malicioso, $sospechoso, $esperado]) {
            $this->assertSame($esperado, $s->clasificar(['malicious' => $malicioso, 'suspicious' => $sospechoso]));
        }
    }
}
