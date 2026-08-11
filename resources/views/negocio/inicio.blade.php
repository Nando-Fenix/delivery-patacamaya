@extends('layouts.negocio')
@section('titulo', 'Panel del negocio — Delivery Patacamaya')
@section('contenido-negocio')
@if(! $negocio)
<div class="empty-state"><h1 class="h4">Aún no tienes negocios registrados</h1><p>Un administrador debe registrar y asociar tu negocio.</p></div>
@else
@if($negocios->count() > 1)<form class="card soft-card p-3 mb-3" method="POST" action="{{ route('negocio.seleccionar', $negocio) }}">@csrf<label class="form-label" for="negocio-seleccionado">Negocio seleccionado</label><select class="form-select" id="negocio-seleccionado" onchange="this.form.action=this.value;this.form.submit()">@foreach($negocios as $opcion)<option value="{{ route('negocio.seleccionar', $opcion) }}" @selected($opcion->id === $negocio->id)>{{ $opcion->nombre }}</option>@endforeach</select></form>@endif
<div class="page-heading"><h1 class="h2">{{ $negocio->nombre }}</h1><p class="text-secondary">{{ $negocio->categoria->nombre }} · {{ $negocio->subcategorias->pluck('nombre')->join(', ') ?: 'Sin subcategorías' }}</p></div>
<div class="row g-3 mb-4">@foreach([['Productos', $negocio->productos_count, 'bi-box-seam'], ['Activos', $negocio->productos_activos_count, 'bi-check-circle'], ['Inactivos', $negocio->productos_inactivos_count, 'bi-pause-circle']] as [$titulo, $valor, $icono])<div class="col-12 col-sm-4"><div class="card metric-card"><div class="card-body d-flex gap-3"><span class="metric-icon"><i class="bi {{ $icono }}"></i></span><div><strong class="fs-3">{{ $valor }}</strong><div class="text-secondary">{{ $titulo }}</div></div></div></div></div>@endforeach</div>
<div class="row g-3">@foreach([['Mi negocio', 'bi-shop', 'negocio.mi-negocio.edit'], ['Horarios', 'bi-clock', 'negocio.horarios.edit'], ['Categorías de productos', 'bi-grid', 'negocio.categorias-producto.index'], ['Productos', 'bi-box-seam', 'negocio.productos.index']] as [$titulo, $icono, $ruta])<div class="col-6 col-md-3"><a class="card soft-card p-3 text-decoration-none text-center h-100" href="{{ route($ruta, $negocio) }}"><i class="bi {{ $icono }} fs-2 text-success"></i><strong class="mt-2 text-dark">{{ $titulo }}</strong></a></div>@endforeach</div>
@endif
@endsection
