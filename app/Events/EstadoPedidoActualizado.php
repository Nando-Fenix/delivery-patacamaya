<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstadoPedidoActualizado implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('negocio.'.$this->pedido->negocio_id),
            new PrivateChannel('cliente.'.$this->pedido->usuario_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedido.estado-actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->pedido->id,
            'negocio_id' => $this->pedido->negocio_id,
            'usuario_id' => $this->pedido->usuario_id,
            'estado' => $this->pedido->estado->value,
            'estado_etiqueta' => $this->pedido->estado->etiqueta(),
            'motivo_rechazo' => $this->pedido->motivo_rechazo,
        ];
    }
}
