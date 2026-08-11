<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\ActualizarCarritoItemRequest;
use App\Http\Requests\Cliente\AgregarCarritoRequest;
use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function index(Request $request): View
    {
        $carrito = $request->user()->carrito()->with(['negocio', 'items.producto'])->first();

        return view('cliente.carrito', compact('carrito'));
    }

    public function store(AgregarCarritoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->load(['negocio.categoria', 'categoria']);
        abort_unless($producto->activo && $producto->disponible && $producto->negocio->activo && $producto->negocio->estado === 'aprobado' && $producto->negocio->categoria->activo && (! $producto->categoria || $producto->categoria->activo), 422);

        $carrito = $request->user()->carrito()->first();
        if ($carrito && $carrito->negocio_id !== $producto->negocio_id) {
            if (! $request->boolean('reemplazar')) {
                return back()->withInput()->with('conflicto_carrito', ['producto' => $producto->id, 'cantidad' => $request->integer('cantidad')]);
            }
            $carrito->items()->delete();
            $carrito->update(['negocio_id' => $producto->negocio_id]);
        }
        $carrito ??= $request->user()->carrito()->create(['negocio_id' => $producto->negocio_id]);
        $item = $carrito->items()->firstOrNew(['producto_id' => $producto->id]);
        $item->cantidad = min(99, ($item->exists ? $item->cantidad : 0) + $request->integer('cantidad'));
        $item->save();

        return back()->with('estado', 'Producto agregado al carrito.');
    }

    public function update(ActualizarCarritoItemRequest $request, CarritoItem $item): RedirectResponse
    {
        $this->propio($request, $item);
        $item->update($request->validated());

        return back()->with('estado', 'Cantidad actualizada.');
    }

    public function destroy(Request $request, CarritoItem $item): RedirectResponse
    {
        $this->propio($request, $item);
        $carrito = $item->carrito;
        $item->delete();
        if (! $carrito->items()->exists()) {
            $carrito->delete();
        }

        return back()->with('estado', 'Producto eliminado.');
    }

    public function vaciar(Request $request): RedirectResponse
    {
        $request->user()->carrito()?->delete();

        return back()->with('estado', 'Carrito vaciado.');
    }

    private function propio(Request $request, CarritoItem $item): void
    {
        abort_unless($item->carrito()->where('usuario_id', $request->user()->id)->exists(), 403);
    }
}
