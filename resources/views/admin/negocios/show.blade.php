@extends('layouts.admin')
@section('titulo', $negocio->nombre . ' — Delivery Patacamaya')
@section('contenido-admin')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-4">
    <div><a class="text-decoration-none small" href="{{ route('administrador.negocios.index') }}"><i class="bi bi-arrow-left me-1"></i>Volver a negocios</a><h1 class="h2 mt-2 mb-1">{{ $negocio->nombre }}</h1><div class="d-flex gap-2"><span class="badge {{ match($negocio->estado) { 'aprobado' => 'text-bg-success', 'rechazado' => 'text-bg-danger', default => 'text-bg-warning' } }}">{{ ucfirst($negocio->estado) }}</span><span class="badge {{ $negocio->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $negocio->activo ? 'Activo' : 'Inactivo' }}</span></div></div>
    <form class="requiere-confirmacion" method="POST" action="{{ route('administrador.negocios.activo', $negocio) }}" data-mensaje="¿Deseas {{ $negocio->activo ? 'desactivar' : 'activar' }} este negocio?">@csrf @method('PATCH')<button class="btn {{ $negocio->activo ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit"><i class="bi {{ $negocio->activo ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>{{ $negocio->activo ? 'Desactivar' : 'Activar' }}</button></form>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <section class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5 mb-4">Información del negocio</h2><dl class="row mb-0 g-3">
            <div class="col-12 col-md-6"><dt class="small text-secondary">Propietario</dt><dd>{{ $negocio->usuario->nombres }} {{ $negocio->usuario->apellidos }}</dd></div>
            <div class="col-12 col-md-6"><dt class="small text-secondary">Categoría</dt><dd><i class="bi {{ $negocio->categoria->icono ?: 'bi-tag' }} me-1"></i>{{ $negocio->categoria->nombre }}</dd></div>
            <div class="col-12"><dt class="small text-secondary">Subcategorías</dt><dd class="d-flex flex-wrap gap-1">@forelse($negocio->subcategorias as $subcategoria)<span class="badge text-bg-light border">{{ $subcategoria->nombre }}</span>@empty Sin subcategorías asignadas. @endforelse</dd><a class="btn btn-sm btn-outline-primary" href="{{ route('administrador.negocios.clasificacion.edit', $negocio) }}"><i class="bi bi-tags me-1"></i>Editar clasificación</a></div>
            <div class="col-12 col-md-6"><dt class="small text-secondary">Teléfono</dt><dd>{{ $negocio->telefono }}</dd></div>
            <div class="col-12 col-md-6"><dt class="small text-secondary">Fecha de registro</dt><dd>{{ $negocio->created_at->format('d/m/Y H:i') }}</dd></div>
            <div class="col-12"><dt class="small text-secondary">Descripción</dt><dd>{{ $negocio->descripcion ?: 'Sin descripción registrada.' }}</dd></div>
            <div class="col-12"><dt class="small text-secondary">Dirección de referencia</dt><dd>{{ $negocio->direccion_referencia ?: 'Sin referencia registrada.' }}</dd></div>
            <div class="col-12"><dt class="small text-secondary">Coordenadas</dt><dd>@if ($negocio->latitud !== null && $negocio->longitud !== null){{ $negocio->latitud }}, {{ $negocio->longitud }}@else Sin coordenadas registradas. @endif</dd></div>
        </dl></div></section>
    </div>
    <div class="col-12 col-xl-4">
        <section class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5 mb-3">Cambiar estado</h2><p class="small text-secondary">Puedes reconsiderar un negocio rechazado o devolverlo a revisión.</p><div class="d-grid gap-2">
            @foreach ([['aprobado', 'Aprobar', 'btn-success', 'bi-check-circle'], ['rechazado', 'Rechazar', 'btn-outline-danger', 'bi-x-circle'], ['pendiente', 'Marcar pendiente', 'btn-outline-warning', 'bi-hourglass-split']] as [$estado, $etiqueta, $clase, $icono])
                @if ($negocio->estado !== $estado)<form class="requiere-confirmacion" method="POST" action="{{ route('administrador.negocios.estado', $negocio) }}" data-mensaje="¿Confirmas cambiar el estado del negocio a {{ $estado }}?">@csrf @method('PATCH')<input type="hidden" name="estado" value="{{ $estado }}"><button class="btn {{ $clase }} w-100" type="submit"><i class="bi {{ $icono }} me-1"></i>{{ $etiqueta }}</button></form>@endif
            @endforeach
        </div></div></section>
    </div>
</div>
@endsection
