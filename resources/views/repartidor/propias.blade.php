@extends('layouts.repartidor')
@section('titulo', 'Mis entregas — Delivery Patacamaya')
@section('contenido-repartidor')
<h1 class="h2">Mis entregas</h1><div class="d-grid gap-3">@forelse($pedidos as $pedido)<a class="card soft-card p-3 text-decoration-none text-dark" href="{{ route('repartidor.entregas.show', $pedido) }}"><div class="d-flex justify-content-between"><strong>Pedido #{{ $pedido->id }}</strong><x-estado-pedido :estado="$pedido->estado"/></div><span>{{ $pedido->negocio->nombre }} · {{ $pedido->zona_nombre }}</span></a>@empty<div class="empty-state">Todavía no aceptaste entregas.</div>@endforelse</div>{{ $pedidos->links() }}
@endsection