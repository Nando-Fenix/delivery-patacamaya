@extends('layouts.cliente')
@section('titulo', 'Mis pedidos — Delivery Patacamaya')

@section('contenido-cliente')
<h1 class="h2">Mis pedidos</h1>
<div class="alert alert-info d-none" id="pedido-estado-aviso" role="status" aria-live="polite">El estado de un pedido fue actualizado.</div>
<div class="d-grid gap-3">
    @forelse ($pedidos as $pedido)
        <a class="card soft-card p-3 text-decoration-none text-dark" id="pedido-{{ $pedido->id }}" href="{{ route('cliente.pedidos.show', $pedido) }}">
            <div class="d-flex justify-content-between">
                <strong>Pedido #{{ $pedido->id }}</strong>
                <span id="pedido-estado-{{ $pedido->id }}"><x-estado-pedido :estado="$pedido->estado"/></span>
            </div>
            <div>{{ $pedido->negocio->nombre }}</div>
            <small class="text-secondary">{{ $pedido->fecha_pedido->format('d/m/Y H:i') }} · Bs {{ number_format((float) $pedido->total, 2, ',', '.') }}</small>
        </a>
    @empty
        <div class="empty-state">Todavía no realizaste pedidos.</div>
    @endforelse
</div>
{{ $pedidos->links() }}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;

    const usuarioId = {{ auth()->id() }};
    const aviso = document.getElementById('pedido-estado-aviso');
    const clases = { pendiente: 'warning', aceptado: 'primary', en_preparacion: 'primary', listo: 'success', entregado: 'success', rechazado: 'danger', cancelado: 'danger' };

    window.Echo.private('cliente.' + usuarioId)
        .listen('.pedido.estado-actualizado', (pedido) => {
            if (Number(pedido.usuario_id) !== usuarioId) return;

            const contenedor = document.getElementById('pedido-estado-' + pedido.id);
            if (!contenedor) return;

            const badge = contenedor.querySelector('.badge');
            if (!badge) return;

            badge.className = 'badge text-bg-' + (clases[pedido.estado] ?? 'secondary');
            badge.innerHTML = '';
            const icono = document.createElement('i');
            icono.className = 'bi bi-circle-fill me-1 small';
            badge.append(icono, document.createTextNode(pedido.estado_etiqueta));
            aviso.classList.remove('d-none');
        });
});
</script>
@endpush