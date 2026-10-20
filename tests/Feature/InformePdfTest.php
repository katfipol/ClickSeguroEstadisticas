<?php

namespace Tests\Feature;

use App\Models\Analisis;
use App\Models\ArchivoVerificado;
use App\Models\Dominio;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Url;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InformePdfTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;
    private Usuario $otroUsuario;
    private TipoRiesgo $riesgo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->riesgo = TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario = Usuario::create([
            'nombre'   => 'Martin Owner',
            'email'    => 'owner@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);

        $this->otroUsuario = Usuario::create([
            'nombre'   => 'Otro Usuario',
            'email'    => 'otro.pdf@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-17: CA-17.1 a CA-17.3 - Descarga de informe PDF de análisis completado */
    public function test_descarga_informe_pdf_de_analisis(): void
    {
        $this->actingAs($this->usuario);

        $dominio = Dominio::create(['nombre' => 'sitio-informe.com']);
        $url = Url::create([
            'id_dominio' => $dominio->id_dominio,
            'url'        => 'https://sitio-informe.com/doc',
            'url_hash'   => hash('sha256', 'https://sitio-informe.com/doc'),
        ]);

        $analisis = Analisis::create([
            'id_usuario'     => $this->usuario->id_usuario,
            'id_url'         => $url->id_url,
            'id_tipo_riesgo' => $this->riesgo->id_tipo_riesgo,
            'estado'         => 'completado',
        ]);

        $response = $this->get(route('informe.analisis', $analisis->id));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** HU-17: CA-17.1 y CA-17.3 - Descarga de informe PDF de archivo verificado */
    public function test_descarga_informe_pdf_de_archivo(): void
    {
        $this->actingAs($this->usuario);

        $archivo = ArchivoVerificado::create([
            'id_usuario'     => $this->usuario->id_usuario,
            'nombre'         => 'documento.pdf',
            'tamano'         => 1024,
            'sha256'         => str_repeat('e', 64),
            'id_tipo_riesgo' => $this->riesgo->id_tipo_riesgo,
        ]);

        $response = $this->get(route('informe.archivo', $archivo->id));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** HU-17: CA-17.4 - Solo el dueño del análisis puede descargarlo */
    public function test_solo_el_dueno_puede_descargar_el_informe(): void
    {
        $dominio = Dominio::create(['nombre' => 'privado.com']);
        $url = Url::create([
            'id_dominio' => $dominio->id_dominio,
            'url'        => 'https://privado.com',
            'url_hash'   => hash('sha256', 'https://privado.com'),
        ]);

        $analisis = Analisis::create([
            'id_usuario'     => $this->usuario->id_usuario,
            'id_url'         => $url->id_url,
            'id_tipo_riesgo' => $this->riesgo->id_tipo_riesgo,
            'estado'         => 'completado',
        ]);

        // El otro usuario intenta descargar el informe
        $this->actingAs($this->otroUsuario);
        $response = $this->get(route('informe.analisis', $analisis->id));
        $response->assertNotFound();
    }
}
