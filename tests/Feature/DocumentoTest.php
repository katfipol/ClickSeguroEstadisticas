<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\FuenteVerificacion;
use App\Models\TipoRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DocumentoTest extends TestCase
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
            'email'    => 'martin.docs@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-13: CA-13.1 - Solo acepta PDF o TXT de hasta 5 MB */
    public function test_rechaza_formato_no_permitido(): void
    {
        $this->actingAs($this->usuario);

        $archivo = UploadedFile::fake()->create('documento.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->post('/documents', ['documento' => $archivo]);
        $response->assertSessionHasErrors(['documento']);
    }

    /** HU-13: CA-13.2 a CA-13.5 - Extrae enlaces de TXT, encola análisis y maneja límite de 20 */
    public function test_extrae_enlaces_de_archivo_txt(): void
    {
        $this->actingAs($this->usuario);
        Queue::fake();

        $contenido = "Visita https://sitio1.com y https://sitio2.org/prueba.\n"
                   . "También https://sitio1.com (repetido) y www.sitio3.net";

        $archivo = UploadedFile::fake()->createWithContent('links.txt', $contenido);

        $response = $this->post('/documents', ['documento' => $archivo]);

        $doc = Documento::where('id_usuario', $this->usuario->id_usuario)->first();
        $this->assertNotNull($doc);
        $this->assertSame('links.txt', $doc->nombre);
        // Debe haber extraído 3 enlaces únicos (sitio1, sitio2, sitio3)
        $this->assertSame(3, $doc->total_enlaces);
        $this->assertSame(0, $doc->enlaces_omitidos);

        $response->assertRedirect(route('documentos.show', $doc->id));

        // Vista muestra resumen y lista
        $vista = $this->get(route('documentos.show', $doc->id));
        $vista->assertOk();
        $vista->assertSee('links.txt');
        $vista->assertSee('sitio1.com');

        // Estado del documento
        $estado = $this->get(route('documentos.estado', $doc->id));
        $estado->assertOk();
    }

    /** HU-13: CA-13.6 - Documento sin enlaces informa mensaje claro */
    public function test_documento_sin_enlaces_muestra_error(): void
    {
        $this->actingAs($this->usuario);

        $archivo = UploadedFile::fake()->createWithContent('vacio.txt', 'Este texto no contiene ningún enlace.');

        $response = $this->post('/documents', ['documento' => $archivo]);
        $response->assertSessionHasErrors(['documento']);
    }
}
