<?php

namespace Tests\Feature;

use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministracionCategoriasNegociosTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $administrador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->administrador = Usuario::where('correo', 'administrador@delivery.test')->firstOrFail();
    }

    public function test_administrador_puede_ver_dashboard_con_metricas(): void
    {
        $this->actingAs($this->administrador)
            ->get('/admin/inicio')
            ->assertOk()
            ->assertSee('Total de negocios')
            ->assertSee('Categorías activas');
    }

    public function test_administrador_puede_listar_y_buscar_categorias(): void
    {
        $this->actingAs($this->administrador)
            ->get('/admin/categorias?buscar=Librería')
            ->assertOk()
            ->assertSee('Librería')
            ->assertDontSee('Ferretería');
    }

    public function test_administrador_puede_ver_formularios_de_categoria(): void
    {
        $categoria = CategoriaNegocio::firstOrFail();

        $this->actingAs($this->administrador)->get('/admin/categorias/create')->assertOk()->assertSee('Nueva categoría');
        $this->actingAs($this->administrador)->get("/admin/categorias/{$categoria->id}/edit")->assertOk()->assertSee('Editar categoría');
    }

    public function test_administrador_puede_crear_categoria(): void
    {
        $this->actingAs($this->administrador)
            ->post('/admin/categorias', [
                'nombre' => 'Soporte técnico',
                'descripcion' => 'Venta y soporte de equipos.',
                'icono' => 'bi-pc-display',
                'activo' => true,
            ])
            ->assertRedirect(route('administrador.categorias.index'));

        $this->assertDatabaseHas('categorias_negocio', ['nombre' => 'Soporte técnico', 'activo' => true]);
    }

    public function test_no_permite_nombres_de_categoria_duplicados(): void
    {
        $this->actingAs($this->administrador)
            ->from('/admin/categorias/create')
            ->post('/admin/categorias', ['nombre' => 'Tienda', 'activo' => true])
            ->assertRedirect('/admin/categorias/create')
            ->assertSessionHasErrors('nombre');
    }

    public function test_administrador_puede_editar_categoria(): void
    {
        $categoria = CategoriaNegocio::where('nombre', 'Tienda')->firstOrFail();

        $this->actingAs($this->administrador)
            ->put("/admin/categorias/{$categoria->id}", [
                'nombre' => 'Tienda local',
                'descripcion' => 'Comercio minorista.',
                'icono' => 'bi-shop',
                'activo' => true,
            ])
            ->assertRedirect(route('administrador.categorias.index'));

        $this->assertDatabaseHas('categorias_negocio', ['id' => $categoria->id, 'nombre' => 'Tienda local']);
    }

    public function test_categoria_con_negocios_puede_desactivarse_sin_eliminarse(): void
    {
        $categoria = CategoriaNegocio::where('nombre', 'Tienda')->firstOrFail();

        $this->actingAs($this->administrador)
            ->patch("/admin/categorias/{$categoria->id}/estado")
            ->assertRedirect();

        $this->assertDatabaseHas('categorias_negocio', ['id' => $categoria->id, 'activo' => false]);
        $this->assertDatabaseCount('negocios', 1);
    }

    public function test_usuarios_no_administradores_no_pueden_manipular_categorias(): void
    {
        $categoria = CategoriaNegocio::firstOrFail();

        foreach (['cliente', 'negocio', 'repartidor'] as $rol) {
            $usuario = Usuario::where('correo', "{$rol}@delivery.test")->firstOrFail();
            $this->actingAs($usuario)->get('/admin/categorias')->assertForbidden();
            $this->actingAs($usuario)->post('/admin/categorias', ['nombre' => 'Prohibida', 'activo' => true])->assertForbidden();
            $this->actingAs($usuario)->patch("/admin/categorias/{$categoria->id}/estado")->assertForbidden();
        }
    }

    public function test_administrador_puede_listar_y_filtrar_negocios(): void
    {
        $negocio = Negocio::firstOrFail();

        $this->actingAs($this->administrador)
            ->get('/admin/negocios?estado=aprobado&activo=1&categoria='.$negocio->categoria_negocio_id)
            ->assertOk()
            ->assertSee($negocio->nombre);
    }

    public function test_administrador_puede_ver_detalle_del_negocio(): void
    {
        $negocio = Negocio::firstOrFail();

        $this->actingAs($this->administrador)
            ->get("/admin/negocios/{$negocio->id}")
            ->assertOk()
            ->assertSee($negocio->nombre)
            ->assertSee('Coordenadas');
    }

    public function test_administrador_puede_aprobar_negocio(): void
    {
        $negocio = Negocio::firstOrFail();
        $negocio->update(['estado' => 'pendiente']);

        $this->actingAs($this->administrador)
            ->patch("/admin/negocios/{$negocio->id}/estado", ['estado' => 'aprobado'])
            ->assertRedirect();

        $this->assertDatabaseHas('negocios', ['id' => $negocio->id, 'estado' => 'aprobado']);
    }

    public function test_administrador_puede_rechazar_y_reconsiderar_negocio(): void
    {
        $negocio = Negocio::firstOrFail();

        $this->actingAs($this->administrador)
            ->patch("/admin/negocios/{$negocio->id}/estado", ['estado' => 'rechazado']);
        $this->assertDatabaseHas('negocios', ['id' => $negocio->id, 'estado' => 'rechazado']);

        $this->actingAs($this->administrador)
            ->patch("/admin/negocios/{$negocio->id}/estado", ['estado' => 'pendiente']);
        $this->assertDatabaseHas('negocios', ['id' => $negocio->id, 'estado' => 'pendiente']);
    }

    public function test_administrador_puede_desactivar_negocio_sin_eliminarlo(): void
    {
        $negocio = Negocio::firstOrFail();

        $this->actingAs($this->administrador)
            ->patch("/admin/negocios/{$negocio->id}/activo")
            ->assertRedirect();

        $this->assertDatabaseHas('negocios', ['id' => $negocio->id, 'activo' => false]);
    }

    public function test_usuarios_no_administradores_no_pueden_manipular_negocios(): void
    {
        $negocio = Negocio::firstOrFail();

        foreach (['cliente', 'negocio', 'repartidor'] as $rol) {
            $usuario = Usuario::where('correo', "{$rol}@delivery.test")->firstOrFail();
            $this->actingAs($usuario)->get('/admin/negocios')->assertForbidden();
            $this->actingAs($usuario)->get("/admin/negocios/{$negocio->id}")->assertForbidden();
            $this->actingAs($usuario)->patch("/admin/negocios/{$negocio->id}/estado", ['estado' => 'aprobado'])->assertForbidden();
            $this->actingAs($usuario)->patch("/admin/negocios/{$negocio->id}/activo")->assertForbidden();
        }
    }
}
