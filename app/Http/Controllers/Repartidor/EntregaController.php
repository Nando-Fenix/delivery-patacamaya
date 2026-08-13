<?php

namespace App\Http\Controllers\Repartidor;

use App\Enums\EstadoPedido;
use App\Events\EntregaAsignada;
use App\Events\EstadoPedidoActualizado;
use App\Events\UbicacionRepartidorActualizada;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EntregaController extends Controller
{
    public function disponibles(): View
    {
        $pedidos = Pedido::query()->with('negocio')
            ->where('estado', EstadoPedido::Listo)->whereNull('repartidor_id')
            ->whereHas('negocio', fn (Builder $query) => $query->where('activo', true)->where('estado', 'aprobado'))
            ->oldest('fecha_pedido')->paginate(15);

        return view('repartidor.disponibles', compact('pedidos'));
    }

    public function propias(Request $request): View
    {
        $pedidos = Pedido::query()->with('negocio')->where('repartidor_id', $request->user()->id)
            ->latest('updated_at')->paginate(15);

        return view('repartidor.propias', compact('pedidos'));
    }

    public function aceptar(Request $request, Pedido $pedido): RedirectResponse
    {
        $asignado = DB::transaction(function () use ($request, $pedido): bool {
            $bloqueado = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);

            if ($bloqueado->estado !== EstadoPedido::Listo || $bloqueado->repartidor_id !== null || ! $bloqueado->negocio()->where('activo', true)->where('estado', 'aprobado')->exists()) {
                return false;
            }

            $bloqueado->update(['repartidor_id' => $request->user()->id]);
            EntregaAsignada::dispatch($bloqueado);

            return true;
        });

        if (! $asignado) {
            throw ValidationException::withMessages(['pedido' => 'La entrega ya fue tomada o dejó de estar disponible.']);
        }

        return redirect()->route('repartidor.entregas.show', $pedido)->with('estado', 'Entrega aceptada correctamente.');
    }

    public function show(Request $request, Pedido $pedido): View
    {
        $this->comprobarAsignacion($request, $pedido);
        $pedido->load(['negocio', 'usuario', 'detalles']);

        return view('repartidor.show', compact('pedido'));
    }

    public function iniciar(Request $request, Pedido $pedido): RedirectResponse
    {
        return $this->cambiarEstado($request, $pedido, EstadoPedido::Listo, EstadoPedido::EnCamino, 'Entrega iniciada.');
    }

    public function entregar(Request $request, Pedido $pedido): RedirectResponse
    {
        return $this->cambiarEstado($request, $pedido, EstadoPedido::EnCamino, EstadoPedido::Entregado, 'Pedido entregado correctamente.');
    }

    public function ubicacion(Request $request, Pedido $pedido): JsonResponse
    {
        $this->comprobarAsignacion($request, $pedido);
        abort_unless($pedido->estado === EstadoPedido::EnCamino, 422);
        $datos = $request->validate([
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'precision' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);
        $pedido->update([
            'repartidor_latitud' => $datos['latitud'],
            'repartidor_longitud' => $datos['longitud'],
            'repartidor_precision' => $datos['precision'] ?? null,
            'ubicacion_repartidor_actualizada_en' => now(),
        ]);
        UbicacionRepartidorActualizada::dispatch($pedido);

        return response()->json(['actualizado_en' => $pedido->ubicacion_repartidor_actualizada_en->toIso8601String()]);
    }

    private function cambiarEstado(Request $request, Pedido $pedido, EstadoPedido $actual, EstadoPedido $nuevo, string $mensaje): RedirectResponse
    {
        $this->comprobarAsignacion($request, $pedido);
        abort_unless($pedido->estado === $actual, 422);
        $pedido->update(['estado' => $nuevo]);
        EstadoPedidoActualizado::dispatch($pedido->refresh());

        return back()->with('estado', $mensaje);
    }

    private function comprobarAsignacion(Request $request, Pedido $pedido): void
    {
        abort_unless($pedido->repartidor_id === $request->user()->id, 403);
    }
}
