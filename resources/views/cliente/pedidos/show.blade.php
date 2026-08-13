@extends('layouts.cliente')
@section('titulo', 'Pedido #'.$pedido->id.' — Delivery Patacamaya')

@section('contenido-cliente')
<a class="btn btn-link px-0" href="{{ route('cliente.pedidos.index') }}">← Mis pedidos</a>
<div class="alert alert-info d-none" id="pedido-estado-aviso" role="status" aria-live="polite">El estado de tu pedido fue actualizado.</div>
<div class="d-flex justify-content-between">
    <h1 class="h2">Pedido #{{ $pedido->id }}</h1>
    <span id="pedido-estado-detalle"><x-estado-pedido :estado="$pedido->estado"/></span>
</div>
<div id="pedido-repartidor" class="alert alert-success {{ $pedido->repartidor ? '' : 'd-none' }}"><strong>Repartidor asignado</strong><div id="pedido-repartidor-nombre">{{ $pedido->repartidor ? trim($pedido->repartidor->nombres.' '.$pedido->repartidor->apellidos) : '' }}</div></div>
<x-seguimiento-repartidor :pedido="$pedido" canal="cliente" :actor-id="auth()->id()"/>
<p>{{ $pedido->negocio->nombre }} · {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</p>
<div class="alert alert-danger {{ $pedido->motivo_rechazo ? '' : 'd-none' }}" id="pedido-motivo-rechazo">
    <strong>Motivo del rechazo:</strong> <span>{{ $pedido->motivo_rechazo }}</span>
</div>
<div class="row g-3">
    <div class="col-lg-7"><div class="card soft-card p-3">
        @foreach ($pedido->detalles as $d)
            <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $d->cantidad }} × {{ $d->nombre_producto }}<small class="d-block">Bs {{ number_format((float) $d->precio_unitario, 2, ',', '.') }} c/u</small></span><strong>Bs {{ number_format((float) $d->subtotal, 2, ',', '.') }}</strong></div>
        @endforeach
    </div></div>
    <div class="col-lg-5"><div class="card soft-card p-3">
        <h2 class="h5">Entrega</h2><strong>{{ $pedido->direccion_nombre }}</strong><span>{{ $pedido->zona_nombre }}</span><span>{{ $pedido->direccion_referencia }}</span><hr><span>Pago: {{ ucfirst($pedido->metodo_pago) }}</span>
        @if ($pedido->observaciones)<p class="mt-2 mb-0"><strong>Observaciones:</strong> {{ $pedido->observaciones }}</p>@endif
        <hr><div class="d-flex justify-content-between"><strong>Total</strong><strong>Bs {{ number_format((float) $pedido->total, 2, ',', '.') }}</strong></div>
    </div></div>
</div>
@if ($pedido->estado === \App\Enums\EstadoPedido::Pendiente)
    <form class="mt-3" id="pedido-cancelar" method="POST" action="{{ route('cliente.pedidos.cancelar', $pedido) }}">@csrf @method('PATCH')<button class="btn btn-outline-danger">Cancelar pedido</button></form>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;

    const usuarioId = {{ auth()->id() }};
    const pedidoId = {{ $pedido->id }};
    const clases = { pendiente: 'warning', aceptado: 'primary', en_preparacion: 'primary', en_camino: 'primary', listo: 'success', entregado: 'success', rechazado: 'danger', cancelado: 'danger' };

    window.Echo.private('cliente.' + usuarioId)
        .listen('.entrega.asignada', (entrega) => {
            if (Number(entrega.usuario_id) === usuarioId && Number(entrega.pedido_id) === pedidoId) {
                document.getElementById('pedido-repartidor-nombre').textContent = entrega.repartidor.nombre;
                document.getElementById('pedido-repartidor').classList.remove('d-none');
            }
        })
        .listen('.pedido.estado-actualizado', (pedido) => {
            if (Number(pedido.usuario_id) !== usuarioId || Number(pedido.id) !== pedidoId) return;

            const badge = document.querySelector('#pedido-estado-detalle .badge');
            if (badge) {
                badge.className = 'badge text-bg-' + (clases[pedido.estado] ?? 'secondary');
                badge.innerHTML = '';
                const icono = document.createElement('i');
                icono.className = 'bi bi-circle-fill me-1 small';
                badge.append(icono, document.createTextNode(pedido.estado_etiqueta));
            }

            const motivo = document.getElementById('pedido-motivo-rechazo');
            if (pedido.estado === 'rechazado' && pedido.motivo_rechazo) {
                motivo.querySelector('span').textContent = pedido.motivo_rechazo;
                motivo.classList.remove('d-none');
            } else {
                motivo.classList.add('d-none');
                motivo.querySelector('span').textContent = '';
            }

            if (pedido.estado !== 'pendiente') document.getElementById('pedido-cancelar')?.remove();
            document.getElementById('pedido-estado-aviso').classList.remove('d-none');
        });
});
</script>
@endpush