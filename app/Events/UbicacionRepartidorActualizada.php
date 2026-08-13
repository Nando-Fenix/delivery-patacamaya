<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UbicacionRepartidorActualizada implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cliente.'.$this->pedido->usuario_id),
            new PrivateChannel('negocio.'.$this->pedido->negocio_id),
            new PrivateChannel('repartidor.'.$this->pedido->repartidor_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'repartidor.ubicacion-actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'latitud' => $this->pedido->repartidor_latitud,
            'longitud' => $this->pedido->repartidor_longitud,
            'precision' => $this->pedido->repartidor_precision,
            'actualizado_en' => $this->pedido->ubicacion_repartidor_actualizada_en->toIso8601String(),
        ];
    }
}
