@props(['pedido', 'canal', 'actorId'])
@php($visible = $pedido->repartidor_id && $pedido->estado === \App\Enums\EstadoPedido::EnCamino)
<section id="seguimiento-repartidor" class="card soft-card p-3 mt-3 {{ $visible ? '' : 'd-none' }}">
    <h2 class="h5">Ubicación del repartidor</h2>
    <div id="mapa-seguimiento" style="height: 320px; border-radius: .75rem;" aria-label="Mapa de seguimiento de la entrega"></div>
    <p class="small text-secondary mt-2 mb-0">Última actualización: <span id="ubicacion-actualizada-en">{{ $pedido->ubicacion_repartidor_actualizada_en?->format('d/m/Y H:i:s') ?? 'Esperando ubicación...' }}</span></p>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pedidoIdSeguimiento = {{ $pedido->id }};
    const canalSeguimiento = @json($canal.'.'.$actorId);
    const entrega = [{{ (float) $pedido->latitud }}, {{ (float) $pedido->longitud }}];
    const inicial = @json($pedido->repartidor_latitud !== null ? [(float) $pedido->repartidor_latitud, (float) $pedido->repartidor_longitud] : null);
    const panel = document.getElementById('seguimiento-repartidor');
    let mapa = null, marcadorRepartidor = null;
    const iniciarMapa = () => {
        if (mapa || !window.L) return;
        try {
            panel.classList.remove('d-none');
            mapa = window.L.map('mapa-seguimiento').setView(inicial ?? entrega, 15);
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(mapa);
            window.L.marker(entrega).addTo(mapa).bindPopup('Lugar de entrega');
            if (inicial) marcadorRepartidor = window.L.marker(inicial).addTo(mapa).bindPopup('Repartidor');
            setTimeout(() => mapa?.invalidateSize(), 100);
        } catch (error) {
            console.warn('No se pudo inicializar el mapa de seguimiento.', error);
            mapa = null;
        }
    };
    const actualizar = (ubicacion) => {
        iniciarMapa();
        const punto = [Number(ubicacion.latitud), Number(ubicacion.longitud)];
        if (marcadorRepartidor) marcadorRepartidor.setLatLng(punto); else marcadorRepartidor = window.L.marker(punto).addTo(mapa).bindPopup('Repartidor');
        mapa.fitBounds(window.L.latLngBounds([entrega, punto]), { padding: [35, 35], maxZoom: 17 });
        document.getElementById('ubicacion-actualizada-en').textContent = new Intl.DateTimeFormat('es-BO', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(ubicacion.actualizado_en));
    };
    if (!panel.classList.contains('d-none')) iniciarMapa();
    if (!window.Echo) return;
    window.Echo.private(canalSeguimiento)
        .listen('.repartidor.ubicacion-actualizada', (ubicacion) => { if (Number(ubicacion.pedido_id) === pedidoIdSeguimiento) actualizar(ubicacion); })
        .listen('.pedido.estado-actualizado', (pedido) => {
            if (Number(pedido.id) !== pedidoIdSeguimiento) return;
            if (pedido.estado === 'en_camino') setTimeout(iniciarMapa, 0);
            if (['entregado', 'cancelado', 'rechazado'].includes(pedido.estado)) panel.classList.add('d-none');
        });
});
</script>
@endpush