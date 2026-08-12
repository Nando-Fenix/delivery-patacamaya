<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoCreado implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('negocio.'.$this->pedido->negocio_id)];
    }

    public function broadcastAs(): string
    {
        return 'pedido.creado';
    }

    public function broadcastWith(): array
    {
        $this->pedido->loadMissing('usuario');

        return [
            'id' => $this->pedido->id,
            'negocio_id' => $this->pedido->negocio_id,
            'estado' => $this->pedido->estado->value,
            'estado_etiqueta' => $this->pedido->estado->etiqueta(),
            'total' => $this->pedido->total,
            'fecha_pedido' => $this->pedido->fecha_pedido->toIso8601String(),
            'cliente' => trim($this->pedido->usuario->nombres.' '.$this->pedido->usuario->apellidos),
        ];
    }
}
