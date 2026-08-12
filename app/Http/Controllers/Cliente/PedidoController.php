<?php

namespace App\Http\Controllers\Cliente;

use App\Enums\EstadoPedido;
use App\Events\EstadoPedidoActualizado;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Request $r): View
    {
        $pedidos = $r->user()->pedidos()->with('negocio')->latest('fecha_pedido')->paginate(12);

        return view('cliente.pedidos.index', compact('pedidos'));
    }

    public function show(Request $r, Pedido $pedido): View
    {
        abort_unless($pedido->usuario_id === $r->user()->id, 403);
        $pedido->load(['negocio', 'detalles']);

        return view('cliente.pedidos.show', compact('pedido'));
    }

    public function cancelar(Request $r, Pedido $pedido): RedirectResponse
    {
        abort_unless($pedido->usuario_id === $r->user()->id, 403);
        abort_unless($pedido->estado === EstadoPedido::Pendiente, 422);
        $pedido->update(['estado' => EstadoPedido::Cancelado]);
        EstadoPedidoActualizado::dispatch($pedido);

        return back()->with('estado', 'Pedido cancelado.');
    }
}
