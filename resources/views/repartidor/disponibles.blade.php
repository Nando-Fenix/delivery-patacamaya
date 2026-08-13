@extends('layouts.repartidor')
@section('titulo', 'Entregas disponibles — Delivery Patacamaya')
@section('contenido-repartidor')
<h1 class="h2">Entregas disponibles</h1><p class="text-secondary">Pedidos listos para recoger. Los datos del cliente se muestran después de aceptar.</p>
<div class="alert alert-info d-none" id="entregas-aviso" role="status" aria-live="polite"></div>
<div class="row g-3" id="entregas-disponibles">
@forelse ($pedidos as $pedido)
<div class="col-12 col-md-6" id="pedido-disponible-{{ $pedido->id }}"><article class="card soft-card h-100 p-3"><div class="d-flex justify-content-between"><strong>Pedido #{{ $pedido->id }}</strong><span class="badge text-bg-success">Listo</span></div><h2 class="h5 mt-2">{{ $pedido->negocio->nombre }}</h2><dl class="row small mb-3"><dt class="col-5">Zona</dt><dd class="col-7">{{ $pedido->zona_nombre }}</dd><dt class="col-5">Delivery</dt><dd class="col-7">Bs {{ number_format((float) $pedido->costo_delivery, 2, ',', '.') }}</dd><dt class="col-5">Hora</dt><dd class="col-7">{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</dd></dl><form class="mt-auto" method="POST" action="{{ route('repartidor.entregas.aceptar', $pedido) }}">@csrf<button class="btn btn-primary w-100 touch-button">Aceptar entrega</button></form></article></div>
@empty <div class="col-12" id="entregas-vacio"><div class="empty-state">No hay entregas disponibles en este momento.</div></div> @endforelse
</div>{{ $pedidos->links() }}
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;
    const listado = document.getElementById('entregas-disponibles');
    const aviso = document.getElementById('entregas-aviso');
    window.Echo.private('repartidores.disponibles')
        .listen('.pedido.disponible-reparto', (pedido) => {
            if (document.getElementById('pedido-disponible-' + pedido.id)) return;
            document.getElementById('entregas-vacio')?.remove();
            const columna = document.createElement('div'); columna.className = 'col-12 col-md-6'; columna.id = 'pedido-disponible-' + pedido.id;
            const tarjeta = document.createElement('article'); tarjeta.className = 'card soft-card h-100 p-3';
            tarjeta.innerHTML = '<div class="d-flex justify-content-between"><strong>Pedido #' + Number(pedido.id) + '</strong><span class="badge text-bg-success">Listo</span></div><h2 class="h5 mt-2"></h2><p class="mb-1"></p><p class="small text-secondary"></p>';
            tarjeta.querySelector('h2').textContent = pedido.negocio; tarjeta.querySelector('p').textContent = 'Zona: ' + pedido.zona; tarjeta.querySelector('.small').textContent = 'Delivery: Bs ' + Number(pedido.costo_delivery).toFixed(2);
            const formulario = document.createElement('form'); formulario.method = 'POST'; formulario.action = '/repartidor/entregas/' + pedido.id + '/aceptar'; formulario.innerHTML = '@csrf<button class="btn btn-primary w-100">Aceptar entrega</button>'; tarjeta.appendChild(formulario); columna.appendChild(tarjeta); listado.prepend(columna);
            aviso.textContent = 'Hay una nueva entrega disponible.'; aviso.classList.remove('d-none');
        })
        .listen('.entrega.asignada', (entrega) => document.getElementById('pedido-disponible-' + entrega.pedido_id)?.remove());
});
</script>
@endpush