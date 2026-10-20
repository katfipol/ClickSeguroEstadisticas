<?php

namespace Tests\Feature;

use App\Models\Dominio;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use App\Services\AnalisisService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FichaDominioTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario = Usuario::create([
            'nombre'   => 'Martin User',
            'email'    => 'martin.domain@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-12: CA-12.1, CA-12.2 y CA-12.3 - Ficha de dominio con alerta de dominio reciente */
    public function test_ficha_dominio_reciente_muestra_alerta(): void
    {
        $this->actingAs($this->usuario);

        $hace5Dias = Carbon::now()->subDays(5);

        Http::fake([
            'www.virustotal.com/api/v3/domains/sitio-nuevo.com' => Http::response([
                'data' => [
                    'attributes' => [
                        'creation_date' => $hace5Dias->timestamp,
                        'registrar'     => 'GoDaddy.com, LLC',
                        'reputation'    => -2,
                    ],
                ],
            ], 200),
            'www.virustotal.com/api/v3/ip_addresses/*' => Http::response([
                'data' => ['attributes' => ['country' => 'US', 'reputation' => 0]],
            ], 200),
            'rdap.org/*' => Http::response([], 404),
        ]);

        $response = $this->get(route('ficha', ['nombre' => 'sitio-nuevo.com']));
        $response->assertOk();
        $response->assertSee('sitio-nuevo.com');
        $response->assertSee('GoDaddy.com, LLC');
        // CA-12.3: Alerta en dominios de menos de 30 días
        $response->assertSee('Dominio muy reciente');
    }

    /** HU-12: CA-12.5 - Reutiliza datos si fueron consultados hace menos de 7 días */
    public function test_reutiliza_datos_en_cache_de_7_dias(): void
    {
        $this->actingAs($this->usuario);

        $dominio = Dominio::create([
            'nombre'         => 'dominio-cacheado.com',
            'fecha_creacion' => '2020-01-01',
            'registrador'    => 'Namecheap',
            'ip'             => '93.184.216.34',
            'pais'           => 'US',
            'reputacion'     => 10,
            'consultado_at'  => now()->subDays(2),
        ]);

        // Sin llamadas HTTP
        Http::fake();

        $response = $this->get(route('ficha', ['nombre' => 'dominio-cacheado.com']));
        $response->assertOk();
        $response->assertSee('dominio-cacheado.com');
        $response->assertSee('Namecheap');

        Http::assertNothingSent();
    }

    /** HU-12: CA-12.1 - Rechaza consulta de IP privada */
    public function test_rechaza_ip_privada(): void
    {
        $this->actingAs($this->usuario);

        $response = $this->get(route('ficha', ['nombre' => '192.168.1.1']));
        $response->assertSee('Solo se pueden consultar direcciones IP públicas.');
    }
}
