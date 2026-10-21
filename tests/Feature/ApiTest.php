<?php

namespace Tests\Feature;

use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario = Usuario::create([
            'nombre'   => 'Api Martin',
            'email'    => 'api.martin@example.com',
            'password' => Hash::make('ClaveApiSegura123#'),
        ]);
    }

    /** REST API: Registro y Login */
    public function test_api_login_y_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'api.martin@example.com',
            'password' => 'ClaveApiSegura123#',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'usuario' => ['id', 'nombre', 'email']]);
    }

    /** REST API: Iniciar análisis de URL */
    public function test_api_analisis_url(): void
    {
        $token = $this->usuario->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/analyze', ['url' => 'https://api-ejemplo.com/test']);

        $response->assertStatus(202);
        $response->assertJsonStructure(['message', 'analisis' => ['id', 'estado', 'url']]);
    }

    /** REST API: Verificación de archivo */
    public function test_api_verificacion_archivo(): void
    {
        $token = $this->usuario->createToken('test')->plainTextToken;
        $hash = str_repeat('f', 64);

        Http::fake([
            "www.virustotal.com/api/v3/files/{$hash}" => Http::response(['error' => 'Not Found'], 404),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/files', [
                'sha256' => $hash,
                'nombre' => 'script.sh',
                'tamano' => 512,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'archivo' => ['id', 'sha256', 'nombre', 'tamano']]);
    }

    /** REST API: Estadísticas personales */
    public function test_api_estadisticas_personales(): void
    {
        $token = $this->usuario->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/statistics/personal');

        $response->assertOk();
        $response->assertJsonStructure(['total', 'seguros', 'sospechosos', 'peligrosos', 'reportes']);
    }
}
