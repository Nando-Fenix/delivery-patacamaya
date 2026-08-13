<?php

namespace App\Http\Controllers\Negocio;

use App\Enums\EstadoPedido;
use App\Events\EstadoPedidoActualizado;
use App\Events\PedidoDisponibleParaReparto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Negocio\CambiarEstadoPedidoRequest;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $pedidos = $negocio->pedidos()->with(['usuario', 'repartidor'])->latest('fecha_pedido')->paginate(15);

        return view('negocio.pedidos.index', compact('negocio', 'pedidos'));
    }

    public function show(Negocio $negocio, Pedido $pedido): View
    {
        abort_unless($pedido->negocio_id === $negocio->id, 404);
        $pedido->load(['usuario', 'detalles', 'repartidor']);

        return view('negocio.pedidos.show', compact('negocio', 'pedido'));
    }

    public function estado(CambiarEstadoPedidoRequest $r, Negocio $negocio, Pedido $pedido): RedirectResponse
    {
        abort_unless($pedido->negocio_id === $negocio->id, 404);
        $nuevo = EstadoPedido::from($r->validated('estado'));
        abort_unless(in_array($nuevo, $pedido->estado->siguientesNegocio(), true), 422);
        $pedido->update(['estado' => $nuevo, 'motivo_rechazo' => $nuevo === EstadoPedido::Rechazado ? $r->validated('motivo_rechazo') : null]);
        EstadoPedidoActualizado::dispatch($pedido);
        if ($nuevo === EstadoPedido::Listo) {
            PedidoDisponibleParaReparto::dispatch($pedido);
        }

        return back()->with('estado', 'Estado del pedido actualizado.');
    }
}
