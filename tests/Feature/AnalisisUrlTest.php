<?php

namespace Tests\Feature;

use App\Jobs\ProcesarAnalisis;
use App\Models\Analisis;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use App\Services\AnalisisService;
use App\Services\VirusTotalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalisisUrlTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Ningún motor de seguridad marcó esta URL.']);
        TipoRiesgo::create(['nombre' => 'Sospechoso', 'descripcion' => 'Pocos motores la marcaron.']);
        TipoRiesgo::create(['nombre' => 'Peligroso', 'descripcion' => 'Varios motores la marcaron como maliciosa.']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario = Usuario::create([
            'nombre'   => 'Martin User',
            'email'    => 'martin.test@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-02: CA-02.2 y CA-02.3 - Rechazo de URL vacía o con formato inválido */
    public function test_rechaza_urls_invalidas_o_vacias(): void
    {
        $this->actingAs($this->usuario);

        // Vacía
        $r1 = $this->post('/analyze', ['url' => '']);
        $r1->assertSessionHasErrors(['url']);

        // Formato no http/https
        $r2 = $this->post('/analyze', ['url' => 'ftp://sitio.com']);
        $r2->assertSessionHasErrors(['url']);

        // Con credenciales
        $r3 = $this->post('/analyze', ['url' => 'http://user:pass@sitio.com']);
        $r3->assertSessionHasErrors(['url']);
    }

    /** HU-02: CA-02.4 y HU-18: CA-18.1 - URL válida inicia análisis pendiente en segundo plano */
    public function test_url_valida_crea_analisis_pendiente(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->actingAs($this->usuario);

        $response = $this->post('/analyze', ['url' => 'https://sitio-seguro.com/index.html']);
        $analisis = Analisis::where('id_usuario', $this->usuario->id_usuario)->latest('id_analisis')->first();

        $this->assertNotNull($analisis);
        $this->assertSame('pendiente', $analisis->estado);
        $response->assertRedirect(route('analisis.show', $analisis->id));
        \Illuminate\Support\Facades\Queue::assertPushed(ProcesarAnalisis::class);

        // HU-18: CA-18.2 - Endpoint de estado
        $estado = $this->get(route('analisis.estado', $analisis->id));
        $estado->assertJson(['terminado' => false, 'estado' => 'pendiente']);
    }

    /** HU-03, HU-05, HU-18: Procesamiento en segundo plano y clasificación Seguro */
    public function test_procesamiento_exitoso_clasifica_url(): void
    {
        $this->actingAs($this->usuario);

        // Mock VirusTotal API
        Http::fake([
            'www.virustotal.com/api/v3/urls/*' => Http::response([
                'data' => [
                    'attributes' => [
                        'last_analysis_stats' => ['malicious' => 0, 'suspicious' => 0],
                        'last_analysis_results' => [
                            'Google Safe Browsing' => ['category' => 'harmless', 'result' => 'clean'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $analisis = app(AnalisisService::class)->crearPendiente($this->usuario, 'https://ejemplo-limpio.org');
        (new ProcesarAnalisis($analisis->id))->handle(app(AnalisisService::class));

        $analisis->refresh();
        $this->assertSame('completado', $analisis->estado);
        $this->assertSame('Seguro', $analisis->tipoRiesgo->nombre);
        $this->assertCount(1, $analisis->detalles);
        $this->assertSame('limpio', $analisis->detalles->first()->resultado);

        // Estado ahora terminado
        $estado = $this->get(route('analisis.estado', $analisis->id));
        $estado->assertJson(['terminado' => true, 'estado' => 'completado']);
    }

    /** HU-03 y CP-12: Fallo del servicio externo deja el análisis marcado como "fallido" */
    public function test_caso_cp_12_fallo_del_servicio_marca_analisis_como_fallido(): void
    {
        $this->actingAs($this->usuario);

        // Simula caída de VirusTotal con error 500
        Http::fake([
            'www.virustotal.com/api/v3/urls/*' => Http::response(['error' => 'Server Error'], 500),
            'www.virustotal.com/api/v3/urls'   => Http::response(['error' => 'Server Error'], 500),
        ]);

        $analisis = app(AnalisisService::class)->crearPendiente($this->usuario, 'https://servicio-caido.com');
        (new ProcesarAnalisis($analisis->id))->handle(app(AnalisisService::class));

        $analisis->refresh();
        // CP-12: Ahora no se borra la fila ni queda vacía, queda marcada como 'fallido' con mensaje claro
        $this->assertSame('fallido', $analisis->estado);
        $this->assertNotNull($analisis->error);

        // Pantalla muestra el estado fallido y opción de reintentar
        $vista = $this->get(route('analisis.show', $analisis->id));
        $vista->assertSee('No se pudo completar el análisis');
        $vista->assertSee('Intentar de nuevo');
    }

    /** HU-07: CA-07.1 a CA-07.3 - Reanálisis crea nuevo registro y enlaza al anterior */
    public function test_reanalisis_crea_nuevo_registro(): void
    {
        $this->actingAs($this->usuario);

        Http::fake([
            'www.virustotal.com/api/v3/urls/*' => Http::response([
                'data' => [
                    'attributes' => [
                        'last_analysis_stats' => ['malicious' => 0, 'suspicious' => 0],
                        'last_analysis_results' => [],
                    ],
                ],
            ], 200),
        ]);

        $analisis1 = app(AnalisisService::class)->crearPendiente($this->usuario, 'https://test-reanalizar.com');
        (new ProcesarAnalisis($analisis1->id))->handle(app(AnalisisService::class));

        $resp = $this->post(route('analisis.reanalizar', $analisis1->id));
        $analisis2 = Analisis::where('id_usuario', $this->usuario->id_usuario)->where('id_analisis', '!=', $analisis1->id_analisis)->first();

        $this->assertNotNull($analisis2);
        $this->assertSame($analisis1->id_analisis, $analisis2->id_analisis_anterior);
        $resp->assertRedirect(route('analisis.show', $analisis2->id));
    }

    /** HU-06: CA-06.4 - Un usuario no puede ver ni eliminar análisis de otro usuario */
    public function test_aislamiento_entre_usuarios(): void
    {
        $otroUsuario = Usuario::create([
            'nombre'   => 'Otro',
            'email'    => 'otro@example.com',
            'password' => Hash::make('Clave1234#'),
        ]);

        $analisisOtro = app(AnalisisService::class)->crearPendiente($otroUsuario, 'https://privado-otro.com');

        $this->actingAs($this->usuario);

        // No puede ver el análisis de otro
        $this->get(route('analisis.show', $analisisOtro->id))->assertNotFound();

        // No puede eliminar el análisis de otro
        $this->delete(route('analisis.destroy', $analisisOtro->id))->assertNotFound();
        $this->assertDatabaseHas('analisis', ['id_analisis' => $analisisOtro->id_analisis]);
    }
}
