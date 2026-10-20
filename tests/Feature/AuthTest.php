<?php

namespace Tests\Feature;

use App\Models\TipoRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TipoRiesgo::create(['nombre' => 'Seguro', 'descripcion' => 'Seguro']);
        TipoRiesgo::create(['nombre' => 'Sospechoso', 'descripcion' => 'Sospechoso']);
        TipoRiesgo::create(['nombre' => 'Peligroso', 'descripcion' => 'Peligroso']);
    }

    /** HU-01: CA-01.1 y CA-01.3 - Registro exitoso con hash */
    public function test_usuario_puede_registrarse_con_datos_validos(): void
    {
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF:0\n", 200),
        ]);

        $response = $this->post('/register', [
            'nombre'                => 'Martin Lopez',
            'email'                 => 'martin@example.com',
            'password'              => 'MiContrasenaSegura#2026',
            'password_confirmation' => 'MiContrasenaSegura#2026',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $usuario = Usuario::where('email', 'martin@example.com')->first();
        $this->assertNotNull($usuario);
        $this->assertTrue(Hash::check('MiContrasenaSegura#2026', $usuario->password));
    }

    /** HU-01: CA-01.2 - Rechaza correo duplicado */
    public function test_rechaza_registro_con_correo_duplicado(): void
    {
        Usuario::create([
            'nombre'   => 'Existente',
            'email'    => 'duplicado@example.com',
            'password' => Hash::make('Secret1234#'),
        ]);

        $response = $this->post('/register', [
            'nombre'                => 'Otro Usuario',
            'email'                 => 'duplicado@example.com',
            'password'              => 'Secret1234#',
            'password_confirmation' => 'Secret1234#',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** HU-01: CA-01.7 & HU-15: CA-15.4 - Rechaza contraseña filtrada en el registro */
    public function test_rechaza_contrasena_filtrada_en_registro(): void
    {
        $sha = strtoupper(sha1('12345678'));
        $sufijo = substr($sha, 5);
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("{$sufijo}:42100\n", 200),
        ]);

        $response = $this->post('/register', [
            'nombre'                => 'Martin',
            'email'                 => 'martin2@example.com',
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('usuario', ['email' => 'martin2@example.com']);
    }

    /** HU-01: CA-01.2 - Inicio de sesión con credenciales válidas y rechazo de inválidas */
    public function test_inicio_de_sesion_correcto_e_incorrecto(): void
    {
        Usuario::create([
            'nombre'   => 'Juan',
            'email'    => 'juan@example.com',
            'password' => Hash::make('ClaveSecreta123'),
        ]);

        // Credenciales inválidas
        $fallo = $this->post('/login', [
            'email'    => 'juan@example.com',
            'password' => 'clave_erronea',
        ]);
        $fallo->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Credenciales válidas
        $exito = $this->post('/login', [
            'email'    => 'juan@example.com',
            'password' => 'ClaveSecreta123',
        ]);
        $exito->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    /** HU-01: CA-01.4 - Rutas protegidas exigen sesión iniciada */
    public function test_rutas_protegidas_exigen_autenticacion(): void
    {
        $rutas = ['/dashboard', '/history', '/statistics', '/report', '/files/new', '/documents/new', '/domain', '/passwords'];
        foreach ($rutas as $ruta) {
            $response = $this->get($ruta);
            $response->assertRedirect(route('login'));
        }
    }

    /** HU-01: CA-01.6 - Cierre de sesión finaliza acceso */
    public function test_cierre_de_sesion(): void
    {
        $usuario = Usuario::create([
            'nombre'   => 'Ana',
            'email'    => 'ana@example.com',
            'password' => Hash::make('ClaveSecreta123'),
        ]);

        $this->actingAs($usuario);
        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
