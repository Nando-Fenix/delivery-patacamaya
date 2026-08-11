@extends('layouts.negocio')
@section('titulo', 'Pedido #'.$pedido->id)

@section('contenido-negocio')
<a class="btn btn-link px-0" href="{{ route('negocio.pedidos.index', $negocio) }}">← Pedidos</a>

<header class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h2 mb-1">Pedido #{{ $pedido->id }}</h1>
        <p class="text-secondary mb-0">{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</p>
    </div>
    <x-estado-pedido :estado="$pedido->estado"/>
</header>

<div class="row g-3">
    <div class="col-lg-7">
        <section class="card soft-card p-3 mb-3">
            <h2 class="h5">Datos generales</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">Cliente</dt>
                <dd class="col-sm-8">{{ $pedido->usuario->nombres }} {{ $pedido->usuario->apellidos }} · {{ $pedido->usuario->telefono }}</dd>
                <dt class="col-sm-4">Método de pago</dt>
                <dd class="col-sm-8">{{ ucfirst($pedido->metodo_pago) }}</dd>
                @if ($pedido->observaciones)
                    <dt class="col-sm-4">Observaciones</dt>
                    <dd class="col-sm-8">{{ $pedido->observaciones }}</dd>
                @endif
            </dl>
        </section>

        <section class="card soft-card p-3">
            <h2 class="h5">Productos</h2>
            @foreach ($pedido->detalles as $detalle)
                <article class="border-bottom py-3">
                    <div class="d-flex justify-content-between gap-3">
                        <strong>{{ $detalle->nombre_producto }}</strong>
                        <strong>Subtotal: Bs {{ number_format((float) $detalle->subtotal, 2, ',', '.') }}</strong>
                    </div>
                    <div class="small text-secondary">Cantidad: {{ $detalle->cantidad }}</div>
                    <div class="small">Bs {{ number_format((float) $detalle->precio_unitario, 2, ',', '.') }} c/u</div>
                </article>
            @endforeach
            <div class="mt-3">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong>Bs {{ number_format((float) $pedido->subtotal, 2, ',', '.') }}</strong></div>
                @if ((float) $pedido->costo_delivery > 0)
                    <div class="d-flex justify-content-between"><span>Costo delivery</span><strong>Bs {{ number_format((float) $pedido->costo_delivery, 2, ',', '.') }}</strong></div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fs-5"><strong>Total</strong><strong>Bs {{ number_format((float) $pedido->total, 2, ',', '.') }}</strong></div>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="card soft-card p-3">
            <h2 class="h5">Entrega</h2>
            <strong>{{ $pedido->direccion_nombre }}</strong>
            <span>{{ $pedido->zona_nombre }}</span>
            <span>{{ $pedido->direccion_referencia }}</span>
            @if ($pedido->latitud !== null && $pedido->longitud !== null)
                <small class="text-secondary mt-2">Coordenadas: {{ $pedido->latitud }}, {{ $pedido->longitud }}</small>
            @endif
        </section>
    </div>
</div>

@if ($pedido->estado === \App\Enums\EstadoPedido::Pendiente)
    <section class="card soft-card p-3 mt-3">
        <h2 class="h5">Acciones del pedido</h2>
        <div class="d-flex flex-wrap gap-3">
            <form method="POST" action="{{ route('negocio.pedidos.estado', [$negocio, $pedido]) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="estado" value="aceptado">
                <button class="btn btn-primary">Aceptar pedido</button>
            </form>
            <form method="POST" action="{{ route('negocio.pedidos.estado', [$negocio, $pedido]) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="estado" value="rechazado">
                <input class="form-control mb-2" name="motivo_rechazo" maxlength="300" required placeholder="Motivo del rechazo">
                <button class="btn btn-outline-danger">Rechazar pedido</button>
            </form>
        </div>
    </section>
@elseif ($pedido->estado === \App\Enums\EstadoPedido::Aceptado)
    <form class="card soft-card p-3 mt-3" method="POST" action="{{ route('negocio.pedidos.estado', [$negocio, $pedido]) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="estado" value="en_preparacion">
        <button class="btn btn-primary">Iniciar preparación</button>
    </form>
@elseif ($pedido->estado === \App\Enums\EstadoPedido::EnPreparacion)
    <form class="card soft-card p-3 mt-3" method="POST" action="{{ route('negocio.pedidos.estado', [$negocio, $pedido]) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="estado" value="listo">
        <button class="btn btn-primary">Marcar como listo</button>
    </form>
@endif
@endsection