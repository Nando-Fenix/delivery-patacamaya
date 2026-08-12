<?php

namespace Tests\Feature;

use App\Enums\EstadoPedido;
use App\Events\EstadoPedidoActualizado;
use App\Events\PedidoCreado;
use App\Models\CategoriaNegocio;
use App\Models\DireccionUsuario;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Zona;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CarritoPedidosTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $propietario;

    private Negocio $negocio;

    private Producto $producto;

    private DireccionUsuario $direccion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-08-10 12:00', 'America/La_Paz'));
        $this->cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        $this->negocio = $this->propietario->negocios()->firstOrFail();
        $this->negocio->horarios()->create(['dia_semana' => 'lunes', 'hora_apertura' => '00:01', 'hora_cierre' => '23:59', 'cerrado' => false]);
        $categoria = $this->negocio->categoriasProducto()->create(['nombre' => 'Comidas', 'activo' => true]);
        $this->producto = $categoria->productos()->create(['negocio_id' => $this->negocio->id, 'nombre' => 'Pollo', 'precio' => '25.50', 'activo' => true, 'disponible' => true]);
        $zona = Zona::create(['nombre' => 'Centro', 'activo' => true]);
        $this->direccion = $this->cliente->direcciones()->create(['zona_id' => $zona->id, 'nombre' => 'Casa', 'direccion_referencia' => 'Puerta azul', 'latitud' => -17.235, 'longitud' => -67.921, 'activo' => true, 'predeterminada' => true]);
    }

    public function test_agrega_actualiza_elimina_y_vacia_carrito(): void
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 2])->assertRedirect();
        $this->get(route('cliente.carrito.index'))->assertOk()->assertSee('Pollo');
        $item = $this->cliente->carrito->items()->firstOrFail();
        $this->assertSame(2, $item->cantidad);
        $this->put(route('cliente.carrito.update', $item), ['cantidad' => 3])->assertRedirect();
        $this->assertDatabaseHas('carrito_items', ['id' => $item->id, 'cantidad' => 3]);
        $this->delete(route('cliente.carrito.destroy', $item))->assertRedirect();
        $this->assertDatabaseCount('carritos', 0);
    }

    public function test_no_agrega_producto_inactivo_no_disponible_o_de_categoria_inactiva(): void
    {
        $this->producto->update(['disponible' => false]);
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1])->assertUnprocessable();
        $this->producto->update(['disponible' => true]);
        $this->producto->categoria->update(['activo' => false]);
        $this->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1])->assertUnprocessable();
    }

    public function test_no_mezcla_negocios_sin_confirmacion_y_reemplaza_con_confirmacion(): void
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1]);
        $otro = $this->otroNegocio('Otro');
        $p = $otro->productos()->create(['nombre' => 'Otro producto', 'precio' => 10, 'activo' => true, 'disponible' => true]);
        $this->post(route('cliente.carrito.store', $p), ['cantidad' => 1])->assertSessionHas('conflicto_carrito');
        $this->assertSame($this->negocio->id, $this->cliente->fresh()->carrito->negocio_id);
        $this->post(route('cliente.carrito.store', $p), ['cantidad' => 1, 'reemplazar' => 1]);
        $this->assertSame($otro->id, $this->cliente->fresh()->carrito->negocio_id);
    }

    public function test_cliente_no_modifica_item_de_carrito_ajeno(): void
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1]);
        $item = $this->cliente->carrito->items()->first();
        $otro = $this->otroUsuarioCliente();
        $this->actingAs($otro)->put(route('cliente.carrito.update', $item), ['cantidad' => 2])->assertForbidden();
    }

    public function test_checkout_sin_zonas_muestra_mensaje_claro(): void
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1]);
        $this->direccion->delete();
        Zona::query()->delete();
        $this->get(route('cliente.checkout.show'))->assertOk()->assertSee('No existen zonas de entrega disponibles actualmente.');
    }

    public function test_checkout_rechaza_direccion_ajena_y_producto_que_cambio(): void
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1]);
        $otro = $this->otroUsuarioCliente();
        $ajena = $otro->direcciones()->create($this->direccion->only(['zona_id', 'nombre', 'direccion_referencia', 'latitud', 'longitud']) + ['activo' => true]);
        $this->post(route('cliente.checkout.store'), ['direccion_id' => $ajena->id, 'metodo_pago' => 'efectivo'])->assertSessionHasErrors('direccion_id');
        $this->producto->update(['disponible' => false]);
        $this->post(route('cliente.checkout.store'), ['direccion_id' => $this->direccion->id, 'metodo_pago' => 'efectivo'])->assertSessionHasErrors('carrito');
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_checkout_crea_historicos_calcula_backend_y_vacia_carrito(): void
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 2]);
        $respuesta = $this->post(route('cliente.checkout.store'), ['direccion_id' => $this->direccion->id, 'metodo_pago' => 'qr', 'observaciones' => 'Sin cebolla']);
        $pedido = Pedido::firstOrFail();
        $respuesta->assertRedirect(route('cliente.pedidos.show', $pedido));
        $this->assertSame('51.00', $pedido->total);
        $this->assertSame('Centro', $pedido->zona_nombre);
        $this->assertDatabaseHas('detalles_pedido', ['pedido_id' => $pedido->id, 'nombre_producto' => 'Pollo', 'precio_unitario' => '25.50', 'cantidad' => 2, 'subtotal' => '51.00']);
        $this->assertDatabaseCount('carritos', 0);
        $this->producto->update(['nombre' => 'Renombrado', 'precio' => 30]);
        $this->direccion->update(['direccion_referencia' => 'Nueva']);
        $this->assertSame('Pollo', $pedido->detalles()->first()->nombre_producto);
        $this->assertSame('Puerta azul', $pedido->fresh()->direccion_referencia);
    }

    public function test_checkout_despacha_pedido_creado_con_el_pedido_confirmado(): void
    {
        Event::fake([PedidoCreado::class]);

        $pedido = $this->crearPedido();

        Event::assertDispatched(
            PedidoCreado::class,
            fn (PedidoCreado $evento) => $evento->pedido->is($pedido)
                && $evento->pedido->negocio_id === $this->negocio->id,
        );
    }

    public function test_negocio_cerrado_impide_pedido(): void
    {
        $this->negocio->horarios()->update(['cerrado' => true]);
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1]);
        $this->post(route('cliente.checkout.store'), ['direccion_id' => $this->direccion->id, 'metodo_pago' => 'efectivo'])->assertSessionHasErrors('carrito');
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_cliente_solo_ve_sus_pedidos_y_cancela_solo_pendiente(): void
    {
        $pedido = $this->crearPedido();
        $otro = $this->otroUsuarioCliente();
        $this->actingAs($otro)->get(route('cliente.pedidos.show', $pedido))->assertForbidden();
        $this->actingAs($this->cliente)->patch(route('cliente.pedidos.cancelar', $pedido))->assertRedirect();
        $this->assertSame(EstadoPedido::Cancelado, $pedido->fresh()->estado);
        $this->patch(route('cliente.pedidos.cancelar', $pedido))->assertUnprocessable();
    }

    public function test_detalle_cliente_contiene_hooks_para_actualizacion_en_tiempo_real(): void
    {
        $pedido = $this->crearPedido();

        $this->actingAs($this->cliente)
            ->get(route('cliente.pedidos.show', $pedido))
            ->assertOk()
            ->assertSee('id="pedido-estado-detalle"', false)
            ->assertSee('id="pedido-motivo-rechazo"', false)
            ->assertSee('id="pedido-cancelar"', false)
            ->assertSee("private('cliente.' + usuarioId)", false)
            ->assertSee("listen('.pedido.estado-actualizado'", false);
    }

    public function test_cancelar_pendiente_despacha_estado_actualizado(): void
    {
        $pedido = $this->crearPedido();
        Event::fake([EstadoPedidoActualizado::class]);

        $this->actingAs($this->cliente)
            ->patch(route('cliente.pedidos.cancelar', $pedido))
            ->assertRedirect();

        $this->assertSame(EstadoPedido::Cancelado, $pedido->fresh()->estado);
        Event::assertDispatched(
            EstadoPedidoActualizado::class,
            fn (EstadoPedidoActualizado $evento) => $evento->pedido->is($pedido)
                && $evento->pedido->estado === EstadoPedido::Cancelado,
        );
    }

    public function test_cliente_ajeno_no_cancela_ni_despacha_evento(): void
    {
        $pedido = $this->crearPedido();
        Event::fake([EstadoPedidoActualizado::class]);

        $this->actingAs($this->otroUsuarioCliente())
            ->patch(route('cliente.pedidos.cancelar', $pedido))
            ->assertForbidden();

        $this->assertSame(EstadoPedido::Pendiente, $pedido->fresh()->estado);
        Event::assertNotDispatched(EstadoPedidoActualizado::class);
    }

    public function test_pedido_no_pendiente_no_se_cancela_ni_despacha_evento(): void
    {
        $pedido = $this->crearPedido();
        $pedido->update(['estado' => EstadoPedido::Aceptado]);
        Event::fake([EstadoPedidoActualizado::class]);

        $this->actingAs($this->cliente)
            ->patch(route('cliente.pedidos.cancelar', $pedido))
            ->assertUnprocessable();

        $this->assertSame(EstadoPedido::Aceptado, $pedido->fresh()->estado);
        Event::assertNotDispatched(EstadoPedidoActualizado::class);
    }

    public function test_negocio_solo_ve_propios_y_respeta_transiciones(): void
    {
        $pedido = $this->crearPedido();
        $otro = $this->otroNegocio('Ajeno');
        $this->actingAs($otro->usuario)->get(route('negocio.pedidos.show', [$otro, $pedido]))->assertNotFound();
        $this->actingAs($this->propietario)->get(route('negocio.pedidos.show', [$this->negocio, $pedido]))
            ->assertOk()->assertSee('Pollo')->assertSee('Cantidad: 1')
            ->assertSee('Bs 25,50 c/u')->assertSee('Total')->assertSee('Aceptar pedido');
        $this->actingAs($this->propietario)->patch(route('negocio.pedidos.estado', [$this->negocio, $pedido]), ['estado' => 'listo'])->assertUnprocessable();
        foreach (['aceptado', 'en_preparacion', 'listo'] as $estado) {
            $this->patch(route('negocio.pedidos.estado', [$this->negocio, $pedido]), ['estado' => $estado])->assertRedirect();
        }
        $this->assertSame(EstadoPedido::Listo, $pedido->fresh()->estado);
    }

    public function test_cambio_valido_del_negocio_despacha_estado_actualizado(): void
    {
        $pedido = $this->crearPedido();
        Event::fake([EstadoPedidoActualizado::class]);

        $this->actingAs($this->propietario)
            ->patch(route('negocio.pedidos.estado', [$this->negocio, $pedido]), ['estado' => 'aceptado'])
            ->assertRedirect();

        Event::assertDispatched(
            EstadoPedidoActualizado::class,
            fn (EstadoPedidoActualizado $evento) => $evento->pedido->is($pedido)
                && $evento->pedido->estado === EstadoPedido::Aceptado,
        );
    }

    public function test_transicion_invalida_del_negocio_no_despacha_estado_actualizado(): void
    {
        $pedido = $this->crearPedido();
        Event::fake([EstadoPedidoActualizado::class]);

        $this->actingAs($this->propietario)
            ->patch(route('negocio.pedidos.estado', [$this->negocio, $pedido]), ['estado' => 'listo'])
            ->assertUnprocessable();

        Event::assertNotDispatched(EstadoPedidoActualizado::class);
    }

    public function test_negocio_puede_rechazar_pendiente_con_motivo(): void
    {
        $pedido = $this->crearPedido();
        Event::fake([EstadoPedidoActualizado::class]);
        $this->actingAs($this->propietario)->patch(route('negocio.pedidos.estado', [$this->negocio, $pedido]), [
            'estado' => 'rechazado',
            'motivo_rechazo' => 'Producto agotado',
        ])->assertRedirect();
        $this->assertSame(EstadoPedido::Rechazado, $pedido->fresh()->estado);
        $this->assertSame('Producto agotado', $pedido->fresh()->motivo_rechazo);
        Event::assertDispatched(EstadoPedidoActualizado::class, function (EstadoPedidoActualizado $evento) use ($pedido) {
            $payload = $evento->broadcastWith();

            return $evento->pedido->is($pedido)
                && $payload['estado'] === 'rechazado'
                && $payload['motivo_rechazo'] === 'Producto agotado';
        });
    }

    private function crearPedido(): Pedido
    {
        $this->actingAs($this->cliente)->post(route('cliente.carrito.store', $this->producto), ['cantidad' => 1]);
        $this->post(route('cliente.checkout.store'), ['direccion_id' => $this->direccion->id, 'metodo_pago' => 'efectivo']);

        return Pedido::firstOrFail();
    }

    private function otroUsuarioCliente(): Usuario
    {
        return Usuario::create(['rol_id' => Rol::where('nombre', 'cliente')->value('id'), 'nombres' => 'Otro', 'apellidos' => 'Cliente', 'telefono' => '78880001', 'correo' => uniqid().'@test.com', 'password' => 'password', 'activo' => true]);
    }

    private function otroNegocio(string $nombre): Negocio
    {
        $u = Usuario::create(['rol_id' => Rol::where('nombre', 'negocio')->value('id'), 'nombres' => $nombre, 'apellidos' => 'Dueño', 'telefono' => '78880002', 'correo' => uniqid().'@test.com', 'password' => 'password', 'activo' => true]);
        $n = Negocio::create(['usuario_id' => $u->id, 'categoria_negocio_id' => CategoriaNegocio::where('activo', true)->first()->id, 'nombre' => $nombre, 'telefono' => '78880002', 'estado' => 'aprobado', 'activo' => true]);
        $n->horarios()->create(['dia_semana' => 'lunes', 'hora_apertura' => '00:01', 'hora_cierre' => '23:59', 'cerrado' => false]);

        return $n;
    }
}
