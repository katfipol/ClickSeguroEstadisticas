<?php

namespace Tests\Feature;

use App\Models\Analisis;
use App\Models\Dominio;
use App\Models\FuenteVerificacion;
use App\Models\Reporte;
use App\Models\TipoRiesgo;
use App\Models\Url;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EstadisticasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario1;
    private Usuario $usuario2;
    private TipoRiesgo $seguro;
    private TipoRiesgo $peligroso;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seguro = TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        $this->peligroso = TipoRiesgo::create(['nombre' => 'Peligroso', 'descripcion' => 'Peligroso']);
        TipoRiesgo::create(['nombre' => 'Sospechoso', 'descripcion' => 'Sospechoso']);
        FuenteVerificacion::firstOrCreate(['nombre' => 'VirusTotal']);

        $this->usuario1 = Usuario::create([
            'nombre'   => 'Usuario Uno',
            'email'    => 'uno@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);

        $this->usuario2 = Usuario::create([
            'nombre'   => 'Usuario Dos',
            'email'    => 'dos@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);
    }

    /** HU-10: CA-10.1 y CA-10.2 - Estadísticas personales solo del usuario */
    public function test_estadisticas_personales_solo_del_usuario(): void
    {
        $this->actingAs($this->usuario1);

        $dom = Dominio::create(['nombre' => 'sitio.com']);
        $url1 = Url::create(['id_dominio' => $dom->id_dominio, 'url' => 'https://sitio.com/1', 'url_hash' => hash('sha256', 'https://sitio.com/1')]);
        $url2 = Url::create(['id_dominio' => $dom->id_dominio, 'url' => 'https://sitio.com/2', 'url_hash' => hash('sha256', 'https://sitio.com/2')]);

        // Usuario 1 tiene 1 seguro y 1 peligroso
        Analisis::create(['id_usuario' => $this->usuario1->id_usuario, 'id_url' => $url1->id_url, 'id_tipo_riesgo' => $this->seguro->id_tipo_riesgo, 'estado' => 'completado']);
        Analisis::create(['id_usuario' => $this->usuario1->id_usuario, 'id_url' => $url2->id_url, 'id_tipo_riesgo' => $this->peligroso->id_tipo_riesgo, 'estado' => 'completado']);

        // Usuario 2 tiene 5 análisis
        for ($i = 0; $i < 5; $i++) {
            Analisis::create(['id_usuario' => $this->usuario2->id_usuario, 'id_url' => $url1->id_url, 'id_tipo_riesgo' => $this->peligroso->id_tipo_riesgo, 'estado' => 'completado']);
        }

        $response = $this->get('/statistics');
        $response->assertOk();
        // Usuario 1 ve total 2, no los 7 del sitio
        $response->assertSee('2');
    }

    /** HU-16: CA-16.1 a CA-16.6 - Estadísticas del sitio solo agrega dominios con >= 2 reportes */
    public function test_estadisticas_globales_dominios_reportados(): void
    {
        $this->actingAs($this->usuario1);

        $domCon2 = Dominio::create(['nombre' => 'sospechoso-frecuente.com']);
        $urlCon2 = Url::create(['id_dominio' => $domCon2->id_dominio, 'url' => 'https://sospechoso-frecuente.com', 'url_hash' => hash('sha256', 'https://sospechoso-frecuente.com')]);

        $domCon1 = Dominio::create(['nombre' => 'aislado.com']);
        $urlCon1 = Url::create(['id_dominio' => $domCon1->id_dominio, 'url' => 'https://aislado.com', 'url_hash' => hash('sha256', 'https://aislado.com')]);

        Reporte::create(['id_usuario' => $this->usuario1->id_usuario, 'id_url' => $urlCon2->id_url, 'estado' => 'pendiente']);
        Reporte::create(['id_usuario' => $this->usuario2->id_usuario, 'id_url' => $urlCon2->id_url, 'estado' => 'pendiente']);
        Reporte::create(['id_usuario' => $this->usuario1->id_usuario, 'id_url' => $urlCon1->id_url, 'estado' => 'pendiente']);

        $response = $this->get('/statistics/site?dias=30');
        $response->assertOk();
        // Aparece el dominio con 2 reportes
        $response->assertSee('sospechoso-frecuente.com');
        // NO aparece el dominio aislado con solo 1 reporte (CA-16.6)
        $response->assertDontSee('aislado.com');
    }
}
