<?php

namespace Tests\Feature;

use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\SubcategoriaNegocio;
use App\Models\Usuario;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploracionClienteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $propietario;

    private CategoriaNegocio $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        $this->categoria = CategoriaNegocio::where('activo', true)->firstOrFail();
    }

    public function test_cliente_accede_al_inicio_y_busca_por_nombre(): void
    {
        $visible = $this->negocio('El Don Sabroso');
        $this->actingAs($this->cliente)->get(route('cliente.inicio'))->assertOk()->assertSee('¿Qué estás buscando?');
        $this->actingAs($this->cliente)->get(route('cliente.buscar', ['buscar' => 'Don']))->assertOk()->assertSee($visible->nombre);
    }

    public function test_solo_aparecen_negocios_aprobados_y_activos(): void
    {
        $visible = $this->negocio('Visible');
        $pendiente = $this->negocio('Pendiente', 'pendiente');
        $rechazado = $this->negocio('Rechazado', 'rechazado');
        $inactivo = $this->negocio('Inactivo', 'aprobado', false);
        $respuesta = $this->actingAs($this->cliente)->get(route('cliente.buscar'));
        $respuesta->assertSee($visible->nombre)->assertDontSee($pendiente->nombre)->assertDontSee($rechazado->nombre)->assertDontSee($inactivo->nombre);
    }

    public function test_filtra_por_categoria_subcategoria_y_combinacion_con_busqueda(): void
    {
        $subcategoria = SubcategoriaNegocio::create(['categoria_negocio_id' => $this->categoria->id, 'nombre' => 'Especial cliente', 'activo' => true]);
        $coincide = $this->negocio('Don Especial');
        $coincide->subcategorias()->attach($subcategoria);
        $otro = $this->negocio('Otro negocio');
        $respuesta = $this->actingAs($this->cliente)->get(route('cliente.buscar', ['buscar' => 'Don', 'categoria' => $this->categoria->id, 'subcategoria' => $subcategoria->id]));
        $respuesta->assertOk()->assertSee($coincide->nombre)->assertDontSee($otro->nombre);
    }

    public function test_cliente_solo_abre_negocio_valido(): void
    {
        $visible = $this->negocio('Perfil visible');
        $oculto = $this->negocio('Perfil oculto', 'pendiente');
        $this->actingAs($this->cliente)->get(route('cliente.negocios.show', $visible))->assertOk()->assertSee('Perfil visible');
        $this->actingAs($this->cliente)->get(route('cliente.negocios.show', $oculto))->assertNotFound();
    }

    public function test_catalogo_expone_solo_categorias_y_productos_activos_y_marca_no_disponible(): void
    {
        $negocio = $this->negocio('Catálogo público');
        $activa = $negocio->categoriasProducto()->create(['nombre' => 'Bebidas', 'activo' => true]);
        $inactiva = $negocio->categoriasProducto()->create(['nombre' => 'Interna secreta', 'activo' => false]);
        $activa->productos()->create(['negocio_id' => $negocio->id, 'nombre' => 'Api disponible', 'precio' => 8, 'activo' => true, 'disponible' => true]);
        $activa->productos()->create(['negocio_id' => $negocio->id, 'nombre' => 'Jugo agotado', 'precio' => 9, 'activo' => true, 'disponible' => false]);
        $activa->productos()->create(['negocio_id' => $negocio->id, 'nombre' => 'Producto desactivado', 'precio' => 10, 'activo' => false]);
        $inactiva->productos()->create(['negocio_id' => $negocio->id, 'nombre' => 'Producto interno', 'precio' => 11, 'activo' => true]);
        $respuesta = $this->actingAs($this->cliente)->get(route('cliente.negocios.show', $negocio));
        $respuesta->assertSee('Bebidas')->assertSee('Api disponible')->assertSee('Jugo agotado')->assertSee('No disponible')->assertDontSee('Producto desactivado')->assertDontSee('Interna secreta')->assertDontSee('Producto interno');
    }

    public function test_busca_producto_dentro_del_negocio(): void
    {
        $negocio = $this->negocio('Productos buscables');
        $categoria = $negocio->categoriasProducto()->create(['nombre' => 'Comidas', 'activo' => true]);
        $categoria->productos()->create(['negocio_id' => $negocio->id, 'nombre' => 'Pollo entero', 'precio' => 65, 'activo' => true]);
        $categoria->productos()->create(['negocio_id' => $negocio->id, 'nombre' => 'Hamburguesa', 'precio' => 25, 'activo' => true]);
        $this->actingAs($this->cliente)->get(route('cliente.negocios.show', [$negocio, 'producto' => 'Pollo']))->assertSee('Pollo entero')->assertDontSee('Hamburguesa');
    }

    public function test_calculo_basico_de_abierto_y_cerrado(): void
    {
        $negocio = $this->negocio('Con horarios');
        $negocio->horarios()->create(['dia_semana' => 'lunes', 'hora_apertura' => '08:00', 'hora_cierre' => '20:00', 'cerrado' => false]);
        $negocio->load('horarios');
        $this->assertTrue($negocio->estaAbierto(Carbon::parse('2026-08-10 10:00', 'America/La_Paz')));
        $this->assertFalse($negocio->estaAbierto(Carbon::parse('2026-08-10 21:00', 'America/La_Paz')));
        $this->assertFalse($negocio->estaAbierto(Carbon::parse('2026-08-11 10:00', 'America/La_Paz')));
    }

    public function test_cliente_accede_al_listado_de_pedidos_vacio(): void
    {
        $this->actingAs($this->cliente)->get(route('cliente.pedidos.index'))->assertOk()->assertSee('Todavía no realizaste pedidos.');
    }

    private function negocio(string $nombre, string $estado = 'aprobado', bool $activo = true): Negocio
    {
        return Negocio::create(['usuario_id' => $this->propietario->id, 'categoria_negocio_id' => $this->categoria->id, 'nombre' => $nombre, 'telefono' => '70000000', 'estado' => $estado, 'activo' => $activo]);
    }
}
