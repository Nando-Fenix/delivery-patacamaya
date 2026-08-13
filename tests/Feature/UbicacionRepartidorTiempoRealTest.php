<?php

namespace Tests\Feature;

use App\Enums\EstadoPedido;
use App\Events\UbicacionRepartidorActualizada;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UbicacionRepartidorTiempoRealTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $repartidor;

    private Usuario $otroRepartidor;

    private Usuario $cliente;

    private Usuario $propietario;

    private Negocio $negocio;

    private Pedido $pedido;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->repartidor = Usuario::where('correo', 'repartidor@delivery.test')->firstOrFail();
        $this->cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        $this->negocio = $this->propietario->negocios()->firstOrFail();
        $this->otroRepartidor = Usuario::create(['rol_id' => Rol::where('nombre', 'repartidor')->value('id'), 'nombres' => 'Otro', 'apellidos' => 'Repartidor', 'telefono' => '79990000', 'correo' => 'otro-gps@test.com', 'password' => 'password', 'activo' => true]);
        $this->pedido = $this->crearPedido(EstadoPedido::EnCamino);
    }

    public function test_repartidor_asignado_en_camino_actualiza_y_reemplaza_ultima_ubicacion(): void
    {
        $this->actingAs($this->repartidor)->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos(-17.20, -67.90, 12.5))->assertOk();
        $primeraFecha = $this->pedido->fresh()->ubicacion_repartidor_actualizada_en;
        $this->travel(6)->seconds();
        $this->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos(-17.21, -67.91, 8))->assertOk();
        $actual = $this->pedido->fresh();

        $this->assertSame('-17.2100000', $actual->repartidor_latitud);
        $this->assertSame('-67.9100000', $actual->repartidor_longitud);
        $this->assertSame('8.00', $actual->repartidor_precision);
        $this->assertTrue($actual->ubicacion_repartidor_actualizada_en->greaterThan($primeraFecha));
        $this->assertDatabaseCount('pedidos', 1);
    }

    public function test_actualizacion_despacha_evento_con_payload_y_canales_privados(): void
    {
        Event::fake([UbicacionRepartidorActualizada::class]);
        $this->actingAs($this->repartidor)->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos())->assertOk();
        Event::assertDispatched(UbicacionRepartidorActualizada::class);

        $evento = new UbicacionRepartidorActualizada($this->pedido->fresh());
        $canales = array_map(fn (PrivateChannel $canal) => $canal->name, $evento->broadcastOn());
        $this->assertContains('private-cliente.'.$this->cliente->id, $canales);
        $this->assertContains('private-negocio.'.$this->negocio->id, $canales);
        $this->assertContains('private-repartidor.'.$this->repartidor->id, $canales);
        $this->assertSame($this->pedido->id, $evento->broadcastWith()['pedido_id']);
        $this->assertArrayNotHasKey('usuario_id', $evento->broadcastWith());
    }

    public function test_otro_repartidor_cliente_y_negocio_no_actualizan_ubicacion(): void
    {
        $this->actingAs($this->otroRepartidor)->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos())->assertForbidden();
        $this->actingAs($this->cliente)->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos())->assertForbidden();
        $this->actingAs($this->propietario)->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos())->assertForbidden();
        $this->assertNull($this->pedido->fresh()->repartidor_latitud);
    }

    public function test_solo_en_camino_permite_actualizar(): void
    {
        foreach ([EstadoPedido::Listo, EstadoPedido::Entregado, EstadoPedido::Cancelado, EstadoPedido::Rechazado] as $estado) {
            $pedido = $this->crearPedido($estado);
            $this->actingAs($this->repartidor)->postJson(route('repartidor.entregas.ubicacion', $pedido), $this->datos())->assertUnprocessable();
            $this->assertNull($pedido->fresh()->repartidor_latitud);
        }
    }

    public function test_rechaza_coordenadas_y_precision_invalidas(): void
    {
        $this->actingAs($this->repartidor)->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos(91, -67))->assertJsonValidationErrors('latitud');
        $this->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos(-17, 181))->assertJsonValidationErrors('longitud');
        $this->postJson(route('repartidor.entregas.ubicacion', $this->pedido), $this->datos(-17, -67, 10001))->assertJsonValidationErrors('precision');
    }

    public function test_cliente_y_negocio_renderizan_mapa_con_ultima_ubicacion(): void
    {
        $this->pedido->update(['repartidor_latitud' => -17.20, 'repartidor_longitud' => -67.90, 'repartidor_precision' => 10, 'ubicacion_repartidor_actualizada_en' => now()]);
        $this->actingAs($this->cliente)->get(route('cliente.pedidos.show', $this->pedido))->assertOk()->assertSee('mapa-seguimiento')->assertSee('repartidor.ubicacion-actualizada');
        $this->actingAs($this->propietario)->get(route('negocio.pedidos.show', [$this->negocio, $this->pedido]))->assertOk()->assertSee('mapa-seguimiento')->assertSee('repartidor.ubicacion-actualizada');
    }

    private function crearPedido(EstadoPedido $estado): Pedido
    {
        return Pedido::create(['usuario_id' => $this->cliente->id, 'negocio_id' => $this->negocio->id, 'repartidor_id' => $this->repartidor->id, 'estado' => $estado, 'subtotal' => 20, 'costo_delivery' => 5, 'total' => 25, 'metodo_pago' => 'efectivo', 'direccion_nombre' => 'Casa', 'direccion_referencia' => 'Puerta azul', 'zona_nombre' => 'Centro', 'latitud' => -17.23, 'longitud' => -67.92, 'fecha_pedido' => now()]);
    }

    private function datos(float $latitud = -17.20, float $longitud = -67.90, float $precision = 10): array
    {
        return compact('latitud', 'longitud', 'precision');
    }
}
