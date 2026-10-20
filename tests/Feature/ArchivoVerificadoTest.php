<?php

namespace Tests\Feature;

use App\Models\ArchivoVerificado;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArchivoVerificadoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        TipoRiesgo::create(['nombre' => 'Sospechoso', 'descripcion' => 'Sospechoso']);
        TipoRiesgo::create(['nombre' => 'Peligroso', 'descripcion' => 'Peligroso']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario = Usuario::create([
            'nombre'   => 'Martin User',
            'email'    => 'martin.files@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-11: CA-11.1 y CA-11.6 - Valida huella SHA-256 y tamaño máximo de 50 MB */
    public function test_validacion_de_campos_de_archivo(): void
    {
        $this->actingAs($this->usuario);

        // SHA inválido
        $r1 = $this->post('/files', [
            'sha256' => 'invalido',
            'nombre' => 'test.exe',
            'tamano' => 1024,
        ]);
        $r1->assertSessionHasErrors(['sha256']);

        // Tamaño superior a 50 MB (52428800 bytes)
        $r2 = $this->post('/files', [
            'sha256' => str_repeat('a', 64),
            'nombre' => 'muy_grande.iso',
            'tamano' => 60000000,
        ]);
        $r2->assertSessionHasErrors(['tamano']);
    }

    /** HU-11: CA-11.3 y CA-11.5 - Archivo conocido con hallazgos maliciosos */
    public function test_verificacion_archivo_conocido_malicioso(): void
    {
        $this->actingAs($this->usuario);
        $hash = str_repeat('b', 64);

        Http::fake([
            "www.virustotal.com/api/v3/files/{$hash}" => Http::response([
                'data' => [
                    'attributes' => [
                        'last_analysis_stats' => ['malicious' => 5, 'suspicious' => 1],
                        'last_analysis_results' => [
                            'Avast' => ['category' => 'malicious', 'result' => 'Win32:Trojan-gen', 'engine_name' => 'Avast'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->post('/files', [
            'sha256' => $hash,
            'nombre' => 'malware.exe',
            'tamano' => 20480,
        ]);

        $archivo = ArchivoVerificado::where('id_usuario', $this->usuario->id_usuario)->first();
        $this->assertNotNull($archivo);
        $this->assertSame('Peligroso', $archivo->tipoRiesgo->nombre);
        $this->assertCount(1, $archivo->detalles);
        $response->assertRedirect(route('archivos.show', $archivo->id));

        $vista = $this->get(route('archivos.show', $archivo->id));
        $vista->assertSee('PELIGROSO');
        $vista->assertSee('malware.exe');
        $vista->assertSee('Avast');
    }

    /** HU-11: CA-11.4 - Archivo desconocido para VirusTotal muestra mensaje correspondiente */
    public function test_verificacion_archivo_desconocido_sin_informacion(): void
    {
        $this->actingAs($this->usuario);
        $hash = str_repeat('c', 64);

        Http::fake([
            "www.virustotal.com/api/v3/files/{$hash}" => Http::response(['error' => 'Not Found'], 404),
        ]);

        $response = $this->post('/files', [
            'sha256' => $hash,
            'nombre' => 'desconocido.bin',
            'tamano' => 1024,
        ]);

        $archivo = ArchivoVerificado::where('id_usuario', $this->usuario->id_usuario)->first();
        $this->assertNotNull($archivo);
        $this->assertNull($archivo->id_tipo_riesgo);

        $vista = $this->get(route('archivos.show', $archivo->id));
        $vista->assertSee('SIN INFORMACIÓN');
        $vista->assertSee('no significa que sea seguro');
    }
}
