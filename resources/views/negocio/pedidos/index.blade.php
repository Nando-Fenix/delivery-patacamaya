@extends('layouts.negocio')
@section('titulo', 'Pedidos — '.$negocio->nombre)

@section('contenido-negocio')
<div class="d-flex align-items-center gap-2 mb-3">
    <h1 class="h2 mb-0">Pedidos</h1>
    <span class="badge text-bg-secondary" id="pedidos-contador">{{ $pedidos->total() }}</span>
</div>

<div class="alert alert-success d-none" id="pedido-nuevo-aviso" role="status" aria-live="polite">
    <i class="bi bi-bell-fill me-2"></i>
    Llegó un nuevo pedido.
</div>

<div class="card soft-card overflow-hidden">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
            <tbody id="pedidos-listado">
                @forelse ($pedidos as $pedido)
                    <tr id="pedido-{{ $pedido->id }}">
                        <td><a href="{{ route('negocio.pedidos.show', [$negocio, $pedido]) }}">#{{ $pedido->id }}</a></td>
                        <td>{{ $pedido->usuario->nombres }} {{ $pedido->usuario->apellidos }}</td>
                        <td>{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</td>
                        <td>Bs {{ number_format((float) $pedido->total, 2, ',', '.') }}</td>
                        <td id="pedido-estado-{{ $pedido->id }}"><x-estado-pedido :estado="$pedido->estado"/><small class="d-block text-success {{ $pedido->repartidor ? '' : 'd-none' }}" id="pedido-repartidor-{{ $pedido->id }}">{{ $pedido->repartidor ? trim($pedido->repartidor->nombres.' '.$pedido->repartidor->apellidos) : '' }}</small></td>
                    </tr>
                @empty
                    <tr id="pedidos-vacio"><td colspan="5"><div class="empty-state">Aún no hay pedidos para este negocio.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $pedidos->links() }}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) {
        return;
    }

    const negocioId = {{ $negocio->id }};
    const listado = document.getElementById('pedidos-listado');
    const aviso = document.getElementById('pedido-nuevo-aviso');
    const contador = document.getElementById('pedidos-contador');

    window.Echo.private('negocio.' + negocioId)
        .listen('.pedido.creado', (pedido) => {
            if (Number(pedido.negocio_id) !== negocioId || document.getElementById('pedido-' + pedido.id)) {
                return;
            }

            document.getElementById('pedidos-vacio')?.remove();

            const fila = document.createElement('tr');
            fila.id = 'pedido-' + pedido.id;

            const enlace = document.createElement('a');
            enlace.href = '/negocio/negocios/' + negocioId + '/pedidos/' + pedido.id;
            enlace.textContent = '#' + pedido.id;

            const celdaPedido = document.createElement('td');
            celdaPedido.appendChild(enlace);

            const celdaCliente = document.createElement('td');
            celdaCliente.textContent = pedido.cliente;

            const celdaFecha = document.createElement('td');
            celdaFecha.textContent = new Intl.DateTimeFormat('es-BO', {
                dateStyle: 'short',
                timeStyle: 'short',
            }).format(new Date(pedido.fecha_pedido));

            const celdaTotal = document.createElement('td');
            celdaTotal.textContent = 'Bs ' + Number(pedido.total).toLocaleString('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const celdaEstado = document.createElement('td');
            celdaEstado.id = 'pedido-estado-' + pedido.id;
            const estado = document.createElement('span');
            estado.className = 'badge text-bg-warning';
            estado.textContent = pedido.estado_etiqueta;
            celdaEstado.appendChild(estado);

            fila.append(celdaPedido, celdaCliente, celdaFecha, celdaTotal, celdaEstado);
            listado.prepend(fila);

            contador.textContent = String(Number(contador.textContent) + 1);
            aviso.classList.remove('d-none');
        })
        .listen('.entrega.asignada', (entrega) => {
            if (Number(entrega.negocio_id) === negocioId && document.getElementById('pedido-' + entrega.pedido_id)) {
                let repartidor = document.getElementById('pedido-repartidor-' + entrega.pedido_id);
                if (!repartidor) {
                    repartidor = document.createElement('small');
                    repartidor.id = 'pedido-repartidor-' + entrega.pedido_id;
                    repartidor.className = 'd-block text-success';
                    document.getElementById('pedido-estado-' + entrega.pedido_id)?.appendChild(repartidor);
                }
                repartidor.textContent = entrega.repartidor.nombre;
                repartidor.classList.remove('d-none');
                aviso.textContent = entrega.repartidor.nombre + ' aceptó una entrega.';
                aviso.classList.remove('d-none');
            }
        })
        .listen('.pedido.estado-actualizado', (pedido) => {
            if (Number(pedido.negocio_id) !== negocioId) {
                return;
            }

            const estado = document.querySelector('#pedido-estado-' + pedido.id + ' .badge');
            if (!estado) {
                return;
            }

            estado.className = pedido.estado === 'cancelado'
                ? 'badge text-bg-danger'
                : 'badge text-bg-secondary';
            estado.textContent = pedido.estado_etiqueta;
        });
});
</script>
@endpush