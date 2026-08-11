<?php

namespace Tests\Feature;

use App\Models\CategoriaNegocio;
use App\Models\CategoriaProducto;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\SubcategoriaNegocio;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GestionNegocioCatalogoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $propietario;

    private Negocio $negocio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        $this->negocio = $this->propietario->negocios()->firstOrFail();
    }

    public function test_propietario_actualiza_solo_los_datos_permitidos_de_su_negocio(): void
    {
        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.update', $this->negocio), [
            'nombre' => 'Sabor Patacamaya', 'descripcion' => 'Menú local', 'telefono' => '71234567',
            'direccion_referencia' => 'Frente a la plaza', 'estado' => 'aprobado', 'activo' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('negocios', ['id' => $this->negocio->id, 'nombre' => 'Sabor Patacamaya', 'estado' => $this->negocio->estado, 'activo' => $this->negocio->activo]);
    }

    public function test_no_puede_acceder_a_un_negocio_ajeno(): void
    {
        $ajeno = $this->crearNegocioAjeno();

        $this->actingAs($this->propietario)->get(route('negocio.mi-negocio.edit', $ajeno))->assertForbidden();
        $this->actingAs($this->propietario)->get(route('negocio.productos.index', $ajeno))->assertForbidden();
    }

    public function test_guarda_los_siete_horarios_y_rechaza_un_cierre_invalido(): void
    {
        $this->actingAs($this->propietario)->get(route('negocio.horarios.edit', $this->negocio))->assertOk();
        $horarios = $this->horariosValidos();
        $this->actingAs($this->propietario)->put(route('negocio.horarios.update', $this->negocio), ['horarios' => $horarios])->assertRedirect();
        $this->assertDatabaseCount('horarios_negocio', 7);

        $horarios[0]['hora_cierre'] = '07:00';
        $this->actingAs($this->propietario)->from(route('negocio.horarios.edit', $this->negocio))->put(route('negocio.horarios.update', $this->negocio), ['horarios' => $horarios])->assertSessionHasErrors('horarios.0.hora_cierre');
    }

    public function test_administra_categorias_sin_borrado_fisico_y_sin_cruzar_negocios(): void
    {
        $this->actingAs($this->propietario)->get(route('negocio.categorias-producto.create', $this->negocio))->assertOk();
        $this->actingAs($this->propietario)->post(route('negocio.categorias-producto.store', $this->negocio), ['nombre' => 'Hamburguesas', 'activo' => 1])->assertRedirect();
        $categoria = CategoriaProducto::whereBelongsTo($this->negocio)->firstOrFail();
        $this->actingAs($this->propietario)->patch(route('negocio.categorias-producto.estado', [$this->negocio, $categoria]))->assertRedirect();
        $this->assertDatabaseHas('categorias_producto', ['id' => $categoria->id, 'activo' => false]);

        $this->actingAs($this->propietario)->post(route('negocio.categorias-producto.store', $this->negocio), ['nombre' => 'Hamburguesas', 'activo' => 1])->assertSessionHasErrors('nombre');
        $ajeno = $this->crearNegocioAjeno();
        $categoriaAjena = $ajeno->categoriasProducto()->create(['nombre' => 'Ajena']);
        $this->actingAs($this->propietario)->get(route('negocio.categorias-producto.edit', [$this->negocio, $categoriaAjena]))->assertForbidden();
    }

    public function test_crea_filtra_y_alterna_estado_y_disponibilidad_de_productos(): void
    {
        Storage::fake('public');
        $categoria = $this->negocio->categoriasProducto()->create(['nombre' => 'Bebidas']);
        $this->actingAs($this->propietario)->post(route('negocio.productos.store', $this->negocio), [
            'categoria_producto_id' => $categoria->id, 'nombre' => 'Api morado', 'descripcion' => 'Vaso grande',
            'precio' => '8.50', 'activo' => 1, 'disponible' => 1, 'imagen' => UploadedFile::fake()->image('api.jpg'),
        ])->assertRedirect();
        $producto = Producto::whereBelongsTo($this->negocio)->firstOrFail();
        Storage::disk('public')->assertExists($producto->imagen);

        $this->actingAs($this->propietario)->get(route('negocio.productos.create', $this->negocio))->assertOk();
        $this->actingAs($this->propietario)->get(route('negocio.productos.edit', [$this->negocio, $producto]))->assertOk();
        $this->actingAs($this->propietario)->get(route('negocio.productos.index', [$this->negocio, 'buscar' => 'Api', 'categoria' => $categoria->id]))->assertOk()->assertSee('Api morado');
        $this->actingAs($this->propietario)->patch(route('negocio.productos.estado', [$this->negocio, $producto]))->assertRedirect();
        $this->actingAs($this->propietario)->patch(route('negocio.productos.disponibilidad', [$this->negocio, $producto]))->assertRedirect();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'activo' => false, 'disponible' => false]);
    }

    public function test_rechaza_precio_invalido_categoria_ajena_y_producto_ajeno(): void
    {
        $ajeno = $this->crearNegocioAjeno();
        $categoriaAjena = $ajeno->categoriasProducto()->create(['nombre' => 'Ajena']);
        $productoAjeno = $ajeno->productos()->create(['nombre' => 'Oculto', 'precio' => 10]);

        $this->actingAs($this->propietario)->post(route('negocio.productos.store', $this->negocio), ['nombre' => 'Inválido', 'precio' => -1, 'categoria_producto_id' => $categoriaAjena->id, 'activo' => 1, 'disponible' => 1])->assertSessionHasErrors(['precio', 'categoria_producto_id']);
        $this->actingAs($this->propietario)->get(route('negocio.productos.edit', [$this->negocio, $productoAjeno]))->assertForbidden();
    }

    public function test_negocio_puede_seleccionar_varias_y_quitar_subcategorias_propias(): void
    {
        $primera = SubcategoriaNegocio::create(['categoria_negocio_id' => $this->negocio->categoria_negocio_id, 'nombre' => 'Pollería', 'activo' => true]);
        $segunda = SubcategoriaNegocio::create(['categoria_negocio_id' => $this->negocio->categoria_negocio_id, 'nombre' => 'Pizzería', 'activo' => true]);

        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.subcategorias.update', $this->negocio), [
            'subcategorias' => [$primera->id, $segunda->id],
        ])->assertRedirect();
        $this->assertEqualsCanonicalizing([$primera->id, $segunda->id], $this->negocio->subcategorias()->pluck('subcategorias_negocio.id')->all());

        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.subcategorias.update', $this->negocio), [
            'subcategorias' => [$segunda->id],
        ])->assertRedirect();
        $this->assertEquals([$segunda->id], $this->negocio->subcategorias()->pluck('subcategorias_negocio.id')->all());

        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.subcategorias.update', $this->negocio), [])->assertRedirect();
        $this->assertDatabaseMissing('negocio_subcategoria', ['negocio_id' => $this->negocio->id]);
    }

    public function test_rechaza_subcategoria_de_otra_categoria_o_inactiva(): void
    {
        $otraCategoria = CategoriaNegocio::whereKeyNot($this->negocio->categoria_negocio_id)->firstOrFail();
        $ajena = SubcategoriaNegocio::create(['categoria_negocio_id' => $otraCategoria->id, 'nombre' => 'Pinturas', 'activo' => true]);
        $inactiva = SubcategoriaNegocio::create(['categoria_negocio_id' => $this->negocio->categoria_negocio_id, 'nombre' => 'Inactiva', 'activo' => false]);

        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.subcategorias.update', $this->negocio), ['subcategorias' => [$ajena->id]])->assertSessionHasErrors('subcategorias.0');
        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.subcategorias.update', $this->negocio), ['subcategorias' => [$inactiva->id]])->assertSessionHasErrors('subcategorias.0');
        $this->assertDatabaseMissing('negocio_subcategoria', ['negocio_id' => $this->negocio->id]);
    }

    public function test_no_puede_modificar_subcategorias_de_negocio_ajeno(): void
    {
        $ajeno = $this->crearNegocioAjeno();
        $subcategoria = SubcategoriaNegocio::create(['categoria_negocio_id' => $ajeno->categoria_negocio_id, 'nombre' => 'Ajena', 'activo' => true]);

        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.subcategorias.update', $ajeno), ['subcategorias' => [$subcategoria->id]])->assertForbidden();
        $this->assertDatabaseMissing('negocio_subcategoria', ['negocio_id' => $ajeno->id]);
    }

    public function test_negocio_no_puede_modificar_su_categoria_general(): void
    {
        $categoriaOriginal = $this->negocio->categoria_negocio_id;
        $otraCategoria = CategoriaNegocio::create(['nombre' => 'Otra categoría', 'activo' => true]);

        $this->actingAs($this->propietario)->put(route('negocio.mi-negocio.update', $this->negocio), [
            'nombre' => $this->negocio->nombre,
            'descripcion' => $this->negocio->descripcion,
            'telefono' => $this->negocio->telefono,
            'direccion_referencia' => $this->negocio->direccion_referencia,
            'categoria_negocio_id' => $otraCategoria->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('negocios', ['id' => $this->negocio->id, 'categoria_negocio_id' => $categoriaOriginal]);
    }

    private function crearNegocioAjeno(): Negocio
    {
        $usuario = Usuario::create(['rol_id' => Rol::where('nombre', 'negocio')->value('id'), 'nombres' => 'Otra', 'apellidos' => 'Persona', 'telefono' => '70000000', 'correo' => fake()->unique()->safeEmail(), 'password' => 'password', 'activo' => true]);

        return Negocio::create(['usuario_id' => $usuario->id, 'categoria_negocio_id' => CategoriaNegocio::firstOrFail()->id, 'nombre' => 'Negocio ajeno', 'telefono' => '70000000']);
    }

    private function horariosValidos(): array
    {
        return collect(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'])->map(fn (string $dia) => ['dia_semana' => $dia, 'hora_apertura' => '08:00', 'hora_cierre' => '20:00', 'cerrado' => false])->all();
    }
}
