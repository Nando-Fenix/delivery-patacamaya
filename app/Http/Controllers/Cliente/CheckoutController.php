<?php

namespace App\Http\Controllers\Cliente;

use App\Enums\EstadoPedido;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\ConfirmarPedidoRequest;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $carrito = $request->user()->carrito()->with(['negocio.horarios', 'items.producto'])->first();
        if (! $carrito || $carrito->items->isEmpty()) {
            return redirect()->route('cliente.carrito.index')->withErrors(['carrito' => 'Tu carrito está vacío.']);
        }
        $hayZonas = Zona::where('activo', true)->exists();
        $direcciones = $request->user()->direcciones()->where('activo', true)->whereHas('zona', fn ($q) => $q->where('activo', true))->with('zona')->orderByDesc('predeterminada')->get();

        return view('cliente.checkout', compact('carrito', 'direcciones', 'hayZonas'));
    }

    public function store(ConfirmarPedidoRequest $request): RedirectResponse
    {
        $pedido = DB::transaction(function () use ($request) {
            $carrito = Carrito::where('usuario_id', $request->user()->id)->lockForUpdate()->first();
            if (! $carrito) {
                throw ValidationException::withMessages(['carrito' => 'Tu carrito está vacío.']);
            }
            $carrito->load(['negocio.horarios', 'items']);
            if ($carrito->items->isEmpty()) {
                throw ValidationException::withMessages(['carrito' => 'Tu carrito está vacío.']);
            }

            $direccion = $request->user()->direcciones()->whereKey($request->integer('direccion_id'))->where('activo', true)->with('zona')->first();
            if (! $direccion || ! $direccion->zona || ! $direccion->zona->activo) {
                throw ValidationException::withMessages(['direccion_id' => 'Selecciona una dirección propia con una zona activa.']);
            }

            $negocio = $carrito->negocio;
            if ($negocio->estado !== 'aprobado' || ! $negocio->activo) {
                throw ValidationException::withMessages(['carrito' => 'El negocio ya no está disponible.']);
            }
            if (! $negocio->estaAbierto()) {
                throw ValidationException::withMessages(['carrito' => 'Este negocio se encuentra cerrado en este momento.']);
            }

            $productos = Producto::whereIn('id', $carrito->items->pluck('producto_id'))->lockForUpdate()->with('categoria')->get()->keyBy('id');
            $detalles = [];
            $subtotalCentavos = 0;
            foreach ($carrito->items as $item) {
                $producto = $productos->get($item->producto_id);
                if (! $producto || $producto->negocio_id !== $negocio->id || ! $producto->activo || ! $producto->disponible || ($producto->categoria && ! $producto->categoria->activo)) {
                    throw ValidationException::withMessages(['carrito' => 'Un producto cambió de disponibilidad. Revisa tu carrito antes de continuar.']);
                }
                $precio = (int) str_replace('.', '', number_format((float) $producto->precio, 2, '.', ''));
                $linea = $precio * $item->cantidad;
                $subtotalCentavos += $linea;
                $detalles[] = ['producto_id' => $producto->id, 'nombre_producto' => $producto->nombre, 'precio_unitario' => number_format($precio / 100, 2, '.', ''), 'cantidad' => $item->cantidad, 'subtotal' => number_format($linea / 100, 2, '.', '')];
            }
            $total = number_format($subtotalCentavos / 100, 2, '.', '');
            $pedido = Pedido::create(['usuario_id' => $request->user()->id, 'negocio_id' => $negocio->id, 'direccion_usuario_id' => $direccion->id, 'estado' => EstadoPedido::Pendiente, 'subtotal' => $total, 'costo_delivery' => '0.00', 'total' => $total, 'metodo_pago' => $request->validated('metodo_pago'), 'observaciones' => $request->validated('observaciones'), 'direccion_nombre' => $direccion->nombre, 'direccion_referencia' => $direccion->direccion_referencia, 'zona_nombre' => $direccion->zona->nombre, 'latitud' => $direccion->latitud, 'longitud' => $direccion->longitud, 'fecha_pedido' => now()]);
            $pedido->detalles()->createMany($detalles);
            $carrito->delete();

            return $pedido;
        });

        return redirect()->route('cliente.pedidos.show', $pedido)->with('estado', 'Pedido confirmado.');
    }
}
