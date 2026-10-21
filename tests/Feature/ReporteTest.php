<?php

namespace Tests\Feature;

use App\Models\FuenteVerificacion;
use App\Models\Reporte;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario = Usuario::create([
            'nombre'   => 'Martin Reporter',
            'email'    => 'reporter@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-09: CA-09.1 a CA-09.3 - Reporte de URL sospechosa */
    public function test_usuario_puede_reportar_url_sospechosa(): void
    {
        $this->actingAs($this->usuario);

        $response = $this->post('/report', [
            'url'    => 'https://phishing-site.example/login',
            'motivo' => 'Suplanta la identidad de un banco conocido',
        ]);

        $response->assertRedirect(route('reportes.create'));
        $response->assertSessionHas('ok');

        $reporte = Reporte::where('id_usuario', $this->usuario->id_usuario)->first();
        $this->assertNotNull($reporte);
        $this->assertSame('pendiente', $reporte->estado);
        $this->assertSame('Suplanta la identidad de un banco conocido', $reporte->motivo);
    }

    /** HU-09: No permite duplicar reporte de la misma URL por el mismo usuario */
    public function test_rechaza_reporte_duplicado(): void
    {
        $this->actingAs($this->usuario);

        $this->post('/report', ['url' => 'https://repetido.com', 'motivo' => 'Primer reporte']);
        $segundo = $this->post('/report', ['url' => 'https://repetido.com', 'motivo' => 'Segundo reporte']);

        $segundo->assertSessionHasErrors(['url']);
        $this->assertSame(1, Reporte::where('id_usuario', $this->usuario->id_usuario)->count());
    }
}
