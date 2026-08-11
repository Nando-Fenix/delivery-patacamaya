<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_un_invitado_puede_ver_el_login(): void
    {
        $this->get('/login')->assertOk()->assertSee('Bienvenido');
    }

    public function test_cada_usuario_es_redirigido_a_su_inicio(): void
    {
        $rutas = [
            'administrador' => '/admin/inicio',
            'cliente' => '/cliente/inicio',
            'negocio' => '/negocio/inicio',
            'repartidor' => '/repartidor/inicio',
        ];

        foreach ($rutas as $rol => $ruta) {
            $this->post('/login', [
                'correo' => "{$rol}@delivery.test",
                'password' => 'Desarrollo123!',
            ])->assertRedirect($ruta);

            $this->post('/logout')->assertRedirect('/login');
        }
    }

    public function test_un_cliente_no_puede_entrar_al_panel_administrador(): void
    {
        $cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();

        $this->actingAs($cliente)->get('/admin/inicio')->assertForbidden();
        $this->actingAs($cliente)->get('/cliente/inicio')->assertOk()->assertSee('cliente');
    }

    public function test_un_negocio_no_puede_entrar_al_panel_repartidor(): void
    {
        $negocio = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();

        $this->actingAs($negocio)->get('/repartidor/inicio')->assertForbidden();
        $this->actingAs($negocio)->get('/negocio/inicio')->assertOk()->assertSee('negocio');
    }

    public function test_el_manifest_y_service_worker_estan_disponibles(): void
    {
        $manifest = public_path('manifest.webmanifest');

        $this->assertFileExists($manifest);
        $this->assertFileExists(public_path('service-worker.js'));
        $this->assertSame('Delivery Patacamaya', json_decode(file_get_contents($manifest), true)['name']);
    }
}
