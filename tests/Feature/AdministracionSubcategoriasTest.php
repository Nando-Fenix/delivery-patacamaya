<?php

namespace Tests\Feature;

use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\SubcategoriaNegocio;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministracionSubcategoriasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = Usuario::where('correo', 'administrador@delivery.test')->firstOrFail();
    }

    public function test_administrador_puede_listar_subcategorias(): void
    {
        $this->actingAs($this->admin)->get('/admin/subcategorias')->assertOk()->assertSee('Pollería');
    }

    public function test_administrador_puede_crear_subcategoria(): void
    {
        $categoria = CategoriaNegocio::where('nombre', 'Restaurante')->firstOrFail();
        $this->actingAs($this->admin)->post('/admin/subcategorias', ['categoria_negocio_id' => $categoria->id, 'nombre' => 'Cafetería', 'icono' => 'bi-cup-hot', 'activo' => true])->assertRedirect();
        $this->assertDatabaseHas('subcategorias_negocio', ['categoria_negocio_id' => $categoria->id, 'nombre' => 'Cafetería']);
    }

    public function test_nombre_es_unico_dentro_de_la_categoria(): void
    {
        $restaurante = CategoriaNegocio::where('nombre', 'Restaurante')->firstOrFail();
        $libreria = CategoriaNegocio::where('nombre', 'Librería')->firstOrFail();
        $this->actingAs($this->admin)->post('/admin/subcategorias', ['categoria_negocio_id' => $restaurante->id, 'nombre' => 'Pollería', 'activo' => true])->assertSessionHasErrors('nombre');
        $this->actingAs($this->admin)->post('/admin/subcategorias', ['categoria_negocio_id' => $libreria->id, 'nombre' => 'Pollería', 'activo' => true])->assertSessionDoesntHaveErrors();
    }

    public function test_administrador_puede_editar_y_desactivar_subcategoria(): void
    {
        $subcategoria = SubcategoriaNegocio::firstOrFail();
        $this->actingAs($this->admin)->put("/admin/subcategorias/{$subcategoria->id}", ['categoria_negocio_id' => $subcategoria->categoria_negocio_id, 'nombre' => 'Pollos', 'activo' => true])->assertRedirect();
        $this->actingAs($this->admin)->patch("/admin/subcategorias/{$subcategoria->id}/estado")->assertRedirect();
        $this->assertDatabaseHas('subcategorias_negocio', ['id' => $subcategoria->id, 'nombre' => 'Pollos', 'activo' => false]);
    }

    public function test_subcategoria_pertenece_a_categoria(): void
    {
        $subcategoria = SubcategoriaNegocio::where('nombre', 'Pizzería')->firstOrFail();
        $this->assertSame('Restaurante', $subcategoria->categoria->nombre);
    }

    public function test_no_administrador_no_puede_administrar_subcategorias(): void
    {
        $cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $subcategoria = SubcategoriaNegocio::firstOrFail();
        $this->actingAs($cliente)->get('/admin/subcategorias')->assertForbidden();
        $this->actingAs($cliente)->post('/admin/subcategorias', [])->assertForbidden();
        $this->actingAs($cliente)->patch("/admin/subcategorias/{$subcategoria->id}/estado")->assertForbidden();
    }

    public function test_negocio_puede_asociarse_a_varias_subcategorias_compatibles(): void
    {
        $negocio = Negocio::firstOrFail();
        $ids = SubcategoriaNegocio::where('categoria_negocio_id', $negocio->categoria_negocio_id)->limit(2)->pluck('id')->all();
        $this->actingAs($this->admin)->put("/admin/negocios/{$negocio->id}/clasificacion", ['categoria_negocio_id' => $negocio->categoria_negocio_id, 'subcategorias' => $ids])->assertRedirect();
        $this->assertCount(2, $negocio->fresh()->subcategorias);
    }

    public function test_administrador_puede_ver_formulario_de_clasificacion(): void
    {
        $negocio = Negocio::firstOrFail();
        $this->actingAs($this->admin)->get("/admin/negocios/{$negocio->id}/clasificacion")->assertOk()->assertSee('Subcategorías');
    }

    public function test_no_se_puede_asociar_subcategoria_de_otra_categoria(): void
    {
        $negocio = Negocio::firstOrFail();
        $incompatible = SubcategoriaNegocio::where('categoria_negocio_id', '!=', $negocio->categoria_negocio_id)->firstOrFail();
        $this->actingAs($this->admin)->put("/admin/negocios/{$negocio->id}/clasificacion", ['categoria_negocio_id' => $negocio->categoria_negocio_id, 'subcategorias' => [$incompatible->id]])->assertSessionHasErrors('subcategorias');
        $this->assertDatabaseMissing('negocio_subcategoria', ['negocio_id' => $negocio->id, 'subcategoria_negocio_id' => $incompatible->id]);
    }

    public function test_cambio_de_categoria_elimina_relaciones_incompatibles(): void
    {
        $negocio = Negocio::firstOrFail();
        $anterior = SubcategoriaNegocio::where('categoria_negocio_id', $negocio->categoria_negocio_id)->firstOrFail();
        $negocio->subcategorias()->attach($anterior);
        $nuevaCategoria = CategoriaNegocio::where('id', '!=', $negocio->categoria_negocio_id)->firstOrFail();
        $nueva = SubcategoriaNegocio::where('categoria_negocio_id', $nuevaCategoria->id)->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/negocios/{$negocio->id}/clasificacion", ['categoria_negocio_id' => $nuevaCategoria->id, 'subcategorias' => [$nueva->id]])->assertRedirect();
        $this->assertDatabaseMissing('negocio_subcategoria', ['negocio_id' => $negocio->id, 'subcategoria_negocio_id' => $anterior->id]);
        $this->assertDatabaseHas('negocio_subcategoria', ['negocio_id' => $negocio->id, 'subcategoria_negocio_id' => $nueva->id]);
    }
}
