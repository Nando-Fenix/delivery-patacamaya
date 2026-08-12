<?php

namespace Tests\Feature;

use App\Enums\EstadoPedido;
use App\Events\EstadoPedidoActualizado;
use App\Events\PedidoCreado;
use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingPedidosTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $propietario;

    private Usuario $cliente;

    private Negocio $negocio;

    protected function setUp(): void
    {
        parent::setUp();
        config(['broadcasting.default' => 'reverb']);
        require base_path('routes/channels.php');
        $this->seed(DatabaseSeeder::class);
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        $this->cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $this->negocio = $this->propietario->negocios()->firstOrFail();
    }

    public function test_propietario_puede_autorizar_canal_de_su_negocio(): void
    {
        $this->actingAs($this->propietario)
            ->post('/broadcasting/auth', $this->datosCanal($this->negocio))
            ->assertOk();
    }

    public function test_otro_propietario_no_puede_autorizar_canal_ajeno(): void
    {
        $otro = $this->otroPropietario();

        $this->actingAs($otro)
            ->post('/broadcasting/auth', $this->datosCanal($this->negocio))
            ->assertForbidden();
    }

    public function test_cliente_no_puede_autorizar_canal_de_negocio(): void
    {
        $this->actingAs($this->cliente)
            ->post('/broadcasting/auth', $this->datosCanal($this->negocio))
            ->assertForbidden();
    }

    public function test_cliente_puede_autorizar_su_canal_privado(): void
    {
        $this->actingAs($this->cliente)
            ->post('/broadcasting/auth', $this->datosCanalCliente($this->cliente->id))
            ->assertOk();
    }

    public function test_cliente_no_puede_autorizar_canal_de_otro_cliente(): void
    {
        $otro = Usuario::create([
            'rol_id' => Rol::where('nombre', 'cliente')->value('id'),
            'nombres' => 'Otro',
            'apellidos' => 'Cliente',
            'telefono' => '78889992',
            'correo' => 'otro-cliente-canal@test.com',
            'password' => 'password',
            'activo' => true,
        ]);

        $this->actingAs($otro)
            ->post('/broadcasting/auth', $this->datosCanalCliente($this->cliente->id))
            ->assertForbidden();
    }

    public function test_negocio_no_puede_autorizar_canal_de_cliente(): void
    {
        $this->actingAs($this->propietario)
            ->post('/broadcasting/auth', $this->datosCanalCliente($this->cliente->id))
            ->assertForbidden();
    }

    public function test_pedido_creado_usa_canal_privado_nombre_y_payload_correctos(): void
    {
        $pedido = $this->pedido();
        $evento = new PedidoCreado($pedido);

        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $evento);
        $this->assertSame('pedido.creado', $evento->broadcastAs());
        $this->assertCount(1, $evento->broadcastOn());
        $this->assertInstanceOf(PrivateChannel::class, $evento->broadcastOn()[0]);
        $this->assertSame('private-negocio.'.$this->negocio->id, $evento->broadcastOn()[0]->name);
        $this->assertSame($pedido->id, $evento->pedido->id);

        $payload = $evento->broadcastWith();
        $this->assertSame($pedido->id, $payload['id']);
        $this->assertSame($this->negocio->id, $payload['negocio_id']);
        $this->assertSame('pendiente', $payload['estado']);
        $this->assertSame('25.50', $payload['total']);
        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('correo', $payload);
    }

    public function test_estado_actualizado_usa_canal_privado_y_payload_cancelado(): void
    {
        $pedido = $this->pedido();
        $pedido->update(['estado' => EstadoPedido::Cancelado]);
        $evento = new EstadoPedidoActualizado($pedido);

        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $evento);
        $this->assertSame('pedido.estado-actualizado', $evento->broadcastAs());
        $this->assertCount(2, $evento->broadcastOn());
        $this->assertInstanceOf(PrivateChannel::class, $evento->broadcastOn()[0]);
        $this->assertInstanceOf(PrivateChannel::class, $evento->broadcastOn()[1]);
        $this->assertSame('private-negocio.'.$this->negocio->id, $evento->broadcastOn()[0]->name);
        $this->assertSame('private-cliente.'.$this->cliente->id, $evento->broadcastOn()[1]->name);

        $payload = $evento->broadcastWith();
        $this->assertSame($pedido->id, $payload['id']);
        $this->assertSame($this->negocio->id, $payload['negocio_id']);
        $this->assertSame($this->cliente->id, $payload['usuario_id']);
        $this->assertSame('cancelado', $payload['estado']);
        $this->assertSame('Cancelado', $payload['estado_etiqueta']);
        $this->assertNull($payload['motivo_rechazo']);
    }

    private function datosCanalCliente(int $usuarioId): array
    {
        return ['socket_id' => '1234.5678', 'channel_name' => 'private-cliente.'.$usuarioId];
    }

    private function datosCanal(Negocio $negocio): array
    {
        return ['socket_id' => '1234.5678', 'channel_name' => 'private-negocio.'.$negocio->id];
    }

    private function otroPropietario(): Usuario
    {
        $usuario = Usuario::create([
            'rol_id' => Rol::where('nombre', 'negocio')->value('id'),
            'nombres' => 'Otro',
            'apellidos' => 'Propietario',
            'telefono' => '78889991',
            'correo' => 'otro-canal@test.com',
            'password' => 'password',
            'activo' => true,
        ]);
        Negocio::create([
            'usuario_id' => $usuario->id,
            'categoria_negocio_id' => CategoriaNegocio::where('activo', true)->firstOrFail()->id,
            'nombre' => 'Negocio ajeno',
            'telefono' => '78889991',
            'estado' => 'aprobado',
            'activo' => true,
        ]);

        return $usuario;
    }

    private function pedido(): Pedido
    {
        return Pedido::create([
            'usuario_id' => $this->cliente->id,
            'negocio_id' => $this->negocio->id,
            'estado' => EstadoPedido::Pendiente,
            'subtotal' => '25.50',
            'costo_delivery' => '0.00',
            'total' => '25.50',
            'metodo_pago' => 'efectivo',
            'direccion_nombre' => 'Casa',
            'direccion_referencia' => 'Puerta azul',
            'zona_nombre' => 'Centro',
            'latitud' => '-17.2350000',
            'longitud' => '-67.9210000',
            'fecha_pedido' => now(),
        ]);
    }
}
