@extends('layouts.cliente')
@section('titulo', 'Carrito — Delivery Patacamaya')

@section('contenido-cliente')
<div class="page-heading">
    <h1 class="h2">Tu carrito</h1>
    <p class="text-secondary">Máximo 99 unidades por producto.</p>
</div>

@error('carrito')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

@if (! $carrito || $carrito->items->isEmpty())
    <div class="empty-state">
        <i class="bi bi-cart fs-1"></i>
        <h2 class="h5 mt-3">Tu carrito está vacío</h2>
        <a class="btn btn-primary mt-2" href="{{ route('cliente.buscar') }}">Explorar negocios</a>
    </div>
@else
    <div class="card soft-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-secondary">Pedido de</small>
                <h2 class="h5 mb-0">{{ $carrito->negocio->nombre }}</h2>
            </div>
            <form method="POST" action="{{ route('cliente.carrito.vaciar') }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Vaciar</button>
            </form>
        </div>
    </div>

    @php
        $total = 0;
    @endphp

    <div class="d-grid gap-3">
        @foreach ($carrito->items as $item)
            @php
                $linea = (float) $item->producto->precio * $item->cantidad;
                $total += $linea;
            @endphp
            <article class="card soft-card p-3">
                <div class="d-flex gap-3">
                    @if ($item->producto->imagen)
                        <img class="product-thumb" src="{{ Storage::url($item->producto->imagen) }}" alt="">
                    @else
                        <span class="product-thumb product-placeholder"><i class="bi bi-image"></i></span>
                    @endif
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between gap-2">
                            <strong>{{ $item->producto->nombre }}</strong>
                            <strong>Bs {{ number_format($linea, 2, ',', '.') }}</strong>
                        </div>
                        <small>Bs {{ number_format((float) $item->producto->precio, 2, ',', '.') }} c/u</small>
                        <div class="d-flex gap-2 mt-2">
                            <form class="d-flex gap-2" method="POST" action="{{ route('cliente.carrito.update', $item) }}">
                                @csrf
                                @method('PUT')
                                <input class="form-control quantity-input" type="number" name="cantidad" min="1" max="99" value="{{ $item->cantidad }}" aria-label="Cantidad de {{ $item->producto->nombre }}">
                                <button class="btn btn-outline-primary">Actualizar</button>
                            </form>
                            <form method="POST" action="{{ route('cliente.carrito.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger" aria-label="Eliminar {{ $item->producto->nombre }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="card soft-card p-3 mt-3">
        <div class="d-flex justify-content-between fs-5">
            <strong>Subtotal</strong>
            <strong>Bs {{ number_format($total, 2, ',', '.') }}</strong>
        </div>
        <div class="d-grid mt-3">
            <a class="btn btn-primary btn-lg" href="{{ route('cliente.checkout.show') }}">Continuar al checkout</a>
        </div>
    </div>
@endif
@endsection