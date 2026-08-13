@extends('layouts.repartidor')
@section('titulo', 'Entrega #'.$pedido->id.' — Delivery Patacamaya')
@section('contenido-repartidor')
<a class="btn btn-link px-0" href="{{ route('repartidor.entregas.propias') }}">← Mis entregas</a>
<header class="d-flex justify-content-between"><h1 class="h2">Pedido #{{ $pedido->id }}</h1><span id="pedido-estado-detalle"><x-estado-pedido :estado="$pedido->estado"/></span></header>
<div class="row g-3"><div class="col-lg-6"><section class="card soft-card p-3 h-100"><h2 class="h5">Recogida</h2><strong>{{ $pedido->negocio->nombre }}</strong><span>{{ $pedido->negocio->direccion_referencia }}</span>@if($pedido->negocio->latitud && $pedido->negocio->longitud)<small>Coordenadas: {{ $pedido->negocio->latitud }}, {{ $pedido->negocio->longitud }}</small>@endif</section></div><div class="col-lg-6"><section class="card soft-card p-3 h-100"><h2 class="h5">Entrega</h2><strong>{{ $pedido->usuario->nombres }} {{ $pedido->usuario->apellidos }}</strong><a href="tel:{{ $pedido->usuario->telefono }}">{{ $pedido->usuario->telefono }}</a><span>{{ $pedido->direccion_nombre }} · {{ $pedido->zona_nombre }}</span><span>{{ $pedido->direccion_referencia }}</span><small>Coordenadas: {{ $pedido->latitud }}, {{ $pedido->longitud }}</small>@if($pedido->observaciones)<p class="mt-2"><strong>Observaciones:</strong> {{ $pedido->observaciones }}</p>@endif</section></div></div>
<div id="gps-panel" class="card soft-card p-3 mt-3 {{ $pedido->estado === \App\Enums\EstadoPedido::EnCamino ? '' : 'd-none' }}"><h2 class="h5">Compartiendo tu ubicación</h2><p id="gps-estado" class="mb-2" role="status">Obteniendo ubicación...</p><button id="gps-reintentar" class="btn btn-outline-primary d-none" type="button">Reintentar GPS</button><small class="text-secondary mt-2">La entrega puede finalizar aunque el GPS no esté disponible.</small></div>
<div id="acciones-entrega" class="mt-3">
@if($pedido->estado === \App\Enums\EstadoPedido::Listo)
<form method="POST" action="{{ route('repartidor.entregas.iniciar', $pedido) }}">@csrf @method('PATCH')<button class="btn btn-primary btn-lg w-100">Iniciar entrega</button></form>
@elseif($pedido->estado === \App\Enums\EstadoPedido::EnCamino)
<form method="POST" action="{{ route('repartidor.entregas.entregar', $pedido) }}">@csrf @method('PATCH')<button class="btn btn-success btn-lg w-100">Marcar como entregado</button></form>
@elseif($pedido->estado === \App\Enums\EstadoPedido::Entregado)
<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Entrega completada.</div>
@endif
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const CONFIG_GPS = { intervaloMinimoMs: 5000, distanciaMinimaMetros: 10, heartbeatMs: 15000 };
    const repartidorId = {{ auth()->id() }};
    const pedidoId = {{ $pedido->id }};
    const endpoint = @json(route('repartidor.entregas.ubicacion', $pedido));
    const token = @json(csrf_token());
    const estadoGps = document.getElementById('gps-estado');
    const reintentar = document.getElementById('gps-reintentar');
    let watchId = null, ultimoEnvio = 0, ultimaPosicion = null, seguimientoActivo = @json($pedido->estado === \App\Enums\EstadoPedido::EnCamino);

    const distanciaMetros = (a, b) => {
        const rad = (valor) => valor * Math.PI / 180;
        const dLat = rad(b.latitud - a.latitud), dLng = rad(b.longitud - a.longitud);
        const x = Math.sin(dLat / 2) ** 2 + Math.cos(rad(a.latitud)) * Math.cos(rad(b.latitud)) * Math.sin(dLng / 2) ** 2;
        return 6371000 * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
    };
    const detener = () => { if (watchId !== null) navigator.geolocation.clearWatch(watchId); watchId = null; seguimientoActivo = false; };
    const enviar = async (posicion) => {
        const actual = { latitud: posicion.coords.latitude, longitud: posicion.coords.longitude };
        const ahora = Date.now();
        if (ahora - ultimoEnvio < CONFIG_GPS.intervaloMinimoMs) return;
        if (ultimaPosicion && distanciaMetros(ultimaPosicion, actual) < CONFIG_GPS.distanciaMinimaMetros && ahora - ultimoEnvio < CONFIG_GPS.heartbeatMs) return;
        try {
            const respuesta = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ ...actual, precision: posicion.coords.accuracy }) });
            if (!respuesta.ok) { if ([403, 422].includes(respuesta.status)) detener(); throw new Error('HTTP ' + respuesta.status); }
            ultimoEnvio = ahora; ultimaPosicion = actual; estadoGps.textContent = posicion.coords.accuracy > 100 ? 'Señal de ubicación débil' : 'Ubicación compartida'; reintentar.classList.add('d-none');
        } catch (error) { estadoGps.textContent = 'No se pudo enviar ubicación'; reintentar.classList.remove('d-none'); }
    };
    const iniciarGps = () => {
        if (!seguimientoActivo) return;
        if (!navigator.geolocation) { estadoGps.textContent = 'GPS no disponible'; reintentar.classList.remove('d-none'); return; }
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        estadoGps.textContent = 'Obteniendo ubicación...';
        watchId = navigator.geolocation.watchPosition(enviar, (error) => { estadoGps.textContent = error.code === 1 ? 'Permiso de ubicación rechazado' : error.code === 3 ? 'Tiempo de espera del GPS agotado' : 'GPS no disponible'; reintentar.classList.remove('d-none'); }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 });
    };
    reintentar.addEventListener('click', iniciarGps);
    window.addEventListener('pagehide', detener);
    if (seguimientoActivo) iniciarGps();
    if (window.Echo) window.Echo.private('repartidor.' + repartidorId).listen('.pedido.estado-actualizado', (pedido) => {
        if (Number(pedido.repartidor_id) !== repartidorId || Number(pedido.id) !== pedidoId) return;
        const badge = document.querySelector('#pedido-estado-detalle .badge');
        if (badge) { badge.className = pedido.estado === 'entregado' ? 'badge text-bg-success' : 'badge text-bg-secondary'; badge.textContent = pedido.estado_etiqueta; }
        if (pedido.estado !== 'en_camino') detener();
    });
});
</script>
@endpush