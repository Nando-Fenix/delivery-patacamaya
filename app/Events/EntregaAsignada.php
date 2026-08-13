<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntregaAsignada implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('repartidores.disponibles'),
            new PrivateChannel('repartidor.'.$this->pedido->repartidor_id),
            new PrivateChannel('cliente.'.$this->pedido->usuario_id),
            new PrivateChannel('negocio.'.$this->pedido->negocio_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'entrega.asignada';
    }

    public function broadcastWith(): array
    {
        $this->pedido->loadMissing('repartidor');

        return [
            'pedido_id' => $this->pedido->id,
            'negocio_id' => $this->pedido->negocio_id,
            'usuario_id' => $this->pedido->usuario_id,
            'repartidor_id' => $this->pedido->repartidor_id,
            'asignado' => true,
            'repartidor' => [
                'id' => $this->pedido->repartidor->id,
                'nombre' => trim($this->pedido->repartidor->nombres.' '.$this->pedido->repartidor->apellidos),
            ],
        ];
    }
}
