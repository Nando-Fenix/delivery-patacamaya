<?php

namespace Tests\Feature;

use App\Enums\EstadoPedido;
use App\Events\EntregaAsignada;
use App\Events\EstadoPedidoActualizado;
use App\Events\PedidoDisponibleParaReparto;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RepartidorEntregasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $repartidor;

    private Usuario $otroRepartidor;

    private Usuario $cliente;

    private Usuario $propietario;

    private Negocio $negocio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->repartidor = Usuario::where('correo', 'repartidor@delivery.test')->firstOrFail();
        $this->otroRepartidor = $this->usuario('repartidor', 'otro-repartidor@test.com');
        $this->cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        $this->negocio = $this->propietario->negocios()->firstOrFail();
    }

    public function test_repartidor_lista_solo_pedidos_listos_no_asignados(): void
    {
        $listo = $this->pedido(EstadoPedido::Listo);
        $pendiente = $this->pedido(EstadoPedido::Pendiente);
        $cancelado = $this->pedido(EstadoPedido::Cancelado);
        $rechazado = $this->pedido(EstadoPedido::Rechazado);
        $asignado = $this->pedido(EstadoPedido::Listo, $this->otroRepartidor);

        $respuesta = $this->actingAs($this->repartidor)->get(route('repartidor.entregas.disponibles'));

        $respuesta->assertOk()->assertSee('Pedido #'.$listo->id)
            ->assertDontSee('Pedido #'.$pendiente->id)->assertDontSee('Pedido #'.$cancelado->id)
            ->assertDontSee('Pedido #'.$rechazado->id)->assertDontSee('Pedido #'.$asignado->id);
    }

    public function test_lista_disponible_no_expone_datos_sensibles(): void
    {
        $this->pedido(EstadoPedido::Listo);

        $this->actingAs($this->repartidor)->get(route('repartidor.entregas.disponibles'))
            ->assertOk()->assertSee('Zona pública')->assertSee($this->negocio->nombre)
            ->assertDontSee($this->cliente->telefono)->assertDontSee('Puerta privada')
            ->assertDontSee('-17.1111111')->assertDontSee('Observación privada');
    }

    public function test_repartidor_acepta_pedido_disponible_y_despacha_evento(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo);
        Event::fake([EntregaAsignada::class]);

        $this->actingAs($this->repartidor)->post(route('repartidor.entregas.aceptar', $pedido))->assertRedirect();

        $this->assertSame($this->repartidor->id, $pedido->fresh()->repartidor_id);
        Event::assertDispatched(EntregaAsignada::class, fn ($evento) => $evento->pedido->id === $pedido->id);
    }

    public function test_segundo_repartidor_no_puede_aceptar_pedido_asignado(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo);
        $this->actingAs($this->repartidor)->post(route('repartidor.entregas.aceptar', $pedido))->assertRedirect();

        $this->actingAs($this->otroRepartidor)->post(route('repartidor.entregas.aceptar', $pedido))
            ->assertSessionHasErrors('pedido');
        $this->assertSame($this->repartidor->id, $pedido->fresh()->repartidor_id);
    }

    public function test_no_acepta_pedido_que_no_esta_listo(): void
    {
        foreach ([EstadoPedido::Pendiente, EstadoPedido::Cancelado, EstadoPedido::Rechazado] as $estado) {
            $pedido = $this->pedido($estado);
            $this->actingAs($this->repartidor)->post(route('repartidor.entregas.aceptar', $pedido))->assertSessionHasErrors('pedido');
            $this->assertNull($pedido->fresh()->repartidor_id);
        }
    }

    public function test_cliente_y_negocio_no_pueden_aceptar_entregas(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo);
        $this->actingAs($this->cliente)->post(route('repartidor.entregas.aceptar', $pedido))->assertForbidden();
        $this->actingAs($this->propietario)->post(route('repartidor.entregas.aceptar', $pedido))->assertForbidden();
        $this->assertNull($pedido->fresh()->repartidor_id);
    }

    public function test_solo_repartidor_asignado_abre_detalle_con_datos_de_entrega(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo, $this->repartidor);

        $this->actingAs($this->repartidor)->get(route('repartidor.entregas.show', $pedido))
            ->assertOk()->assertSee($this->cliente->telefono)->assertSee('Puerta privada')
            ->assertSee('-17.1111111')->assertSee('Observación privada');
        $this->actingAs($this->otroRepartidor)->get(route('repartidor.entregas.show', $pedido))->assertForbidden();
    }

    public function test_canales_de_repartidor_son_privados_y_restringidos_por_rol(): void
    {
        config(['broadcasting.default' => 'reverb']);
        require base_path('routes/channels.php');
        $datos = ['socket_id' => '123.456', 'channel_name' => 'private-repartidores.disponibles'];
        $this->actingAs($this->repartidor)->post('/broadcasting/auth', $datos)->assertOk();
        $this->actingAs($this->cliente)->post('/broadcasting/auth', $datos)->assertForbidden();
        $individual = ['socket_id' => '123.456', 'channel_name' => 'private-repartidor.'.$this->repartidor->id];
        $this->actingAs($this->otroRepartidor)->post('/broadcasting/auth', $individual)->assertForbidden();
    }

    public function test_eventos_de_reparto_usan_canales_y_payload_minimo(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo, $this->repartidor);
        $disponible = new PedidoDisponibleParaReparto($pedido);
        $this->assertSame('private-repartidores.disponibles', $disponible->broadcastOn()[0]->name);
        $this->assertArrayNotHasKey('direccion_referencia', $disponible->broadcastWith());
        $this->assertArrayNotHasKey('telefono', $disponible->broadcastWith());

        $asignada = new EntregaAsignada($pedido);
        $canales = array_map(fn (PrivateChannel $canal) => $canal->name, $asignada->broadcastOn());
        $this->assertContains('private-repartidores.disponibles', $canales);
        $this->assertContains('private-repartidor.'.$this->repartidor->id, $canales);
        $this->assertContains('private-cliente.'.$this->cliente->id, $canales);
        $this->assertContains('private-negocio.'.$this->negocio->id, $canales);
    }

    public function test_marcar_listo_despacha_disponibilidad_para_repartidores(): void
    {
        $pedido = $this->pedido(EstadoPedido::EnPreparacion);
        Event::fake([PedidoDisponibleParaReparto::class]);

        $this->actingAs($this->propietario)->patch(route('negocio.pedidos.estado', [$this->negocio, $pedido]), ['estado' => 'listo'])->assertRedirect();

        Event::assertDispatched(PedidoDisponibleParaReparto::class, fn ($evento) => $evento->pedido->id === $pedido->id);
    }

    public function test_pedido_pertenece_a_repartidor_asignado(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo, $this->repartidor);

        $this->assertTrue($pedido->repartidor->is($this->repartidor));
        $this->assertTrue($this->repartidor->entregas->contains($pedido));
    }

    public function test_cliente_y_negocio_visualizan_repartidor_solo_despues_de_asignacion(): void
    {
        $sinAsignar = $this->pedido(EstadoPedido::Listo);
        $asignado = $this->pedido(EstadoPedido::Listo, $this->repartidor);
        $nombre = trim($this->repartidor->nombres.' '.$this->repartidor->apellidos);

        $this->actingAs($this->cliente)->get(route('cliente.pedidos.show', $sinAsignar))->assertOk()->assertDontSee($nombre);
        $this->get(route('cliente.pedidos.show', $asignado))->assertOk()->assertSee($nombre);
        $this->actingAs($this->propietario)->get(route('negocio.pedidos.show', [$this->negocio, $asignado]))->assertOk()->assertSee($nombre);
    }

    public function test_repartidor_asignado_completa_flujo_y_despacha_estados(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo, $this->repartidor);
        Event::fake([EstadoPedidoActualizado::class]);

        $this->actingAs($this->repartidor)->patch(route('repartidor.entregas.iniciar', $pedido))->assertRedirect();
        $this->assertSame(EstadoPedido::EnCamino, $pedido->fresh()->estado);
        Event::assertDispatched(EstadoPedidoActualizado::class, fn ($evento) => $evento->pedido->estado === EstadoPedido::EnCamino);

        Event::fake([EstadoPedidoActualizado::class]);
        $this->patch(route('repartidor.entregas.entregar', $pedido))->assertRedirect();
        $this->assertSame(EstadoPedido::Entregado, $pedido->fresh()->estado);
        Event::assertDispatched(EstadoPedidoActualizado::class, fn ($evento) => $evento->pedido->estado === EstadoPedido::Entregado);
    }

    public function test_otro_repartidor_cliente_y_negocio_no_cambian_estado_de_entrega(): void
    {
        $pedido = $this->pedido(EstadoPedido::Listo, $this->repartidor);
        $this->actingAs($this->otroRepartidor)->patch(route('repartidor.entregas.iniciar', $pedido))->assertForbidden();
        $this->actingAs($this->cliente)->patch(route('repartidor.entregas.iniciar', $pedido))->assertForbidden();
        $this->actingAs($this->propietario)->patch(route('repartidor.entregas.iniciar', $pedido))->assertForbidden();
        $this->assertSame(EstadoPedido::Listo, $pedido->fresh()->estado);
    }

    public function test_rechaza_saltos_y_transiciones_inversas(): void
    {
        $listo = $this->pedido(EstadoPedido::Listo, $this->repartidor);
        $this->actingAs($this->repartidor)->patch(route('repartidor.entregas.entregar', $listo))->assertUnprocessable();
        $entregado = $this->pedido(EstadoPedido::Entregado, $this->repartidor);
        $this->patch(route('repartidor.entregas.iniciar', $entregado))->assertUnprocessable();
        foreach ([EstadoPedido::Cancelado, EstadoPedido::Rechazado] as $estado) {
            $pedido = $this->pedido($estado, $this->repartidor);
            $this->patch(route('repartidor.entregas.iniciar', $pedido))->assertUnprocessable();
        }
    }

    public function test_eventos_incluyen_repartidor_seguro_y_canales_de_participantes(): void
    {
        $pedido = $this->pedido(EstadoPedido::EnCamino, $this->repartidor);
        $asignada = new EntregaAsignada($pedido);
        $payloadAsignacion = $asignada->broadcastWith();
        $this->assertSame($this->repartidor->id, $payloadAsignacion['repartidor']['id']);
        $this->assertSame(trim($this->repartidor->nombres.' '.$this->repartidor->apellidos), $payloadAsignacion['repartidor']['nombre']);
        $this->assertArrayNotHasKey('telefono', $payloadAsignacion['repartidor']);
        $this->assertArrayNotHasKey('correo', $payloadAsignacion['repartidor']);

        $estado = new EstadoPedidoActualizado($pedido);
        $canales = array_map(fn (PrivateChannel $canal) => $canal->name, $estado->broadcastOn());
        $this->assertContains('private-cliente.'.$this->cliente->id, $canales);
        $this->assertContains('private-negocio.'.$this->negocio->id, $canales);
        $this->assertContains('private-repartidor.'.$this->repartidor->id, $canales);
        $this->assertSame('en_camino', $estado->broadcastWith()['estado']);
    }

    public function test_payload_y_canales_son_correctos_para_en_camino_y_entregado(): void
    {
        foreach ([EstadoPedido::EnCamino, EstadoPedido::Entregado] as $estadoPedido) {
            $pedido = $this->pedido($estadoPedido, $this->repartidor);
            $evento = new EstadoPedidoActualizado($pedido);
            $payload = $evento->broadcastWith();
            $canales = array_map(fn (PrivateChannel $canal) => $canal->name, $evento->broadcastOn());

            $this->assertSame($pedido->id, $payload['id']);
            $this->assertSame($estadoPedido->value, $payload['estado']);
            $this->assertSame($estadoPedido->etiqueta(), $payload['estado_etiqueta']);
            $this->assertSame($this->negocio->id, $payload['negocio_id']);
            $this->assertSame($this->cliente->id, $payload['usuario_id']);
            $this->assertSame($this->repartidor->id, $payload['repartidor_id']);
            $this->assertContains('private-cliente.'.$this->cliente->id, $canales);
            $this->assertContains('private-negocio.'.$this->negocio->id, $canales);
        }
    }

    public function test_listados_y_detalles_contienen_listeners_de_estado(): void
    {
        $pedido = $this->pedido(EstadoPedido::EnCamino, $this->repartidor);

        $this->actingAs($this->cliente)->get(route('cliente.pedidos.index'))->assertOk()->assertSee("listen('.pedido.estado-actualizado'", false)->assertSee('pedido-estado-'.$pedido->id);
        $this->get(route('cliente.pedidos.show', $pedido))->assertOk()->assertSee("listen('.pedido.estado-actualizado'", false)->assertSee('pedido-estado-detalle');
        $this->actingAs($this->propietario)->get(route('negocio.pedidos.index', $this->negocio))->assertOk()->assertSee("listen('.pedido.estado-actualizado'", false)->assertSee('pedido-estado-'.$pedido->id);
        $this->get(route('negocio.pedidos.show', [$this->negocio, $pedido]))->assertOk()->assertSee("listen('.pedido.estado-actualizado'", false)->assertSee('pedido-estado-detalle')->assertSee('setTimeout(iniciarMapa, 0)', false);
    }

    private function pedido(EstadoPedido $estado, ?Usuario $repartidor = null): Pedido
    {
        return Pedido::create([
            'usuario_id' => $this->cliente->id, 'negocio_id' => $this->negocio->id,
            'repartidor_id' => $repartidor?->id, 'estado' => $estado,
            'subtotal' => 20, 'costo_delivery' => 5, 'total' => 25, 'metodo_pago' => 'efectivo',
            'observaciones' => 'Observación privada', 'direccion_nombre' => 'Casa',
            'direccion_referencia' => 'Puerta privada', 'zona_nombre' => 'Zona pública',
            'latitud' => '-17.1111111', 'longitud' => '-67.2222222', 'fecha_pedido' => now(),
        ]);
    }

    private function usuario(string $rol, string $correo): Usuario
    {
        return Usuario::create([
            'rol_id' => Rol::where('nombre', $rol)->value('id'), 'nombres' => 'Otro', 'apellidos' => 'Repartidor',
            'telefono' => '79999999', 'correo' => $correo, 'password' => 'password', 'activo' => true,
        ]);
    }
}
