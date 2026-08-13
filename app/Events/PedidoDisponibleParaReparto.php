<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoDisponibleParaReparto implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('repartidores.disponibles')];
    }

    public function broadcastAs(): string
    {
        return 'pedido.disponible-reparto';
    }

    public function broadcastWith(): array
    {
        $this->pedido->loadMissing('negocio');

        return [
            'id' => $this->pedido->id,
            'negocio' => $this->pedido->negocio->nombre,
            'zona' => $this->pedido->zona_nombre,
            'costo_delivery' => $this->pedido->costo_delivery,
            'fecha_pedido' => $this->pedido->fecha_pedido->toIso8601String(),
        ];
    }
}
