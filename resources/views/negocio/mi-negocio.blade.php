@extends('layouts.negocio')
@section('titulo', 'Mi negocio — Delivery Patacamaya')
@section('contenido-negocio')
<div class="page-heading"><h1 class="h2">Mi negocio</h1><p class="text-secondary">Administra la información pública y especialidades de tu negocio.</p></div>

<section class="card soft-card p-3 p-sm-4 mb-4">
    <div class="mb-4"><h2 class="h4 mb-1">Información general</h2><p class="text-secondary mb-0">Actualiza los datos visibles de tu negocio.</p></div>
    <div class="category-summary mb-4"><small class="text-secondary">Categoría principal</small><div class="fw-semibold fs-5">{{ $negocio->categoria->nombre }}</div><div class="small text-secondary"><i class="bi bi-lock me-1"></i>Administrada por Delivery Patacamaya. Solo administración puede modificarla.</div></div>
    <div class="row g-3 mb-4"><div class="col-6"><small class="text-secondary">Aprobación</small><div>{{ ucfirst($negocio->estado) }}</div></div><div class="col-6"><small class="text-secondary">Estado</small><div>{{ $negocio->activo ? 'Activo' : 'Inactivo' }}</div></div>@if($negocio->latitud !== null)<div class="col-12"><small class="text-secondary">Coordenadas</small><div>{{ $negocio->latitud }}, {{ $negocio->longitud }}</div></div>@endif</div>
    <form method="POST" action="{{ route('negocio.mi-negocio.update', $negocio) }}">@csrf @method('PUT')<div class="row g-3"><div class="col-12"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $negocio->nombre) }}" required></div><div class="col-12"><label class="form-label" for="descripcion">Descripción</label><textarea class="form-control" id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $negocio->descripcion) }}</textarea></div><div class="col-md-6"><label class="form-label" for="telefono">Teléfono</label><input class="form-control" id="telefono" name="telefono" value="{{ old('telefono', $negocio->telefono) }}" required></div><div class="col-md-6"><label class="form-label" for="direccion_referencia">Dirección de referencia</label><input class="form-control" id="direccion_referencia" name="direccion_referencia" value="{{ old('direccion_referencia', $negocio->direccion_referencia) }}"></div></div><button class="btn btn-primary touch-button mt-4 w-100">Guardar información general</button></form>
</section>

<a class="btn btn-outline-primary touch-button w-100 mb-4" href="{{ route('negocio.ubicacion.edit',$negocio) }}">Configurar ubicación y zona</a>
<section class="card soft-card p-3 p-sm-4">
    <div class="mb-3"><h2 class="h4 mb-1">Subcategorías</h2><p class="text-secondary mb-0">Selecciona las especialidades o tipos de productos que ofrece tu negocio.</p></div>
    @if($subcategoriasDisponibles->isEmpty())
        <div class="empty-state border"><i class="bi bi-tags fs-2"></i><p class="mb-0 mt-2">No existen subcategorías disponibles para esta categoría.</p></div>
    @else
        <form method="POST" action="{{ route('negocio.mi-negocio.subcategorias.update', $negocio) }}">@csrf @method('PUT')
            <div class="subcategory-options">@foreach($subcategoriasDisponibles as $subcategoria)<label class="subcategory-option"><input class="form-check-input" type="checkbox" name="subcategorias[]" value="{{ $subcategoria->id }}" @checked(in_array($subcategoria->id, old('subcategorias', $negocio->subcategorias->modelKeys())))><span><strong>{{ $subcategoria->nombre }}</strong>@if($subcategoria->descripcion)<small>{{ $subcategoria->descripcion }}</small>@endif</span></label>@endforeach</div>
            @error('subcategorias.*')<div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>@enderror
            <button class="btn btn-primary touch-button mt-4 w-100">Guardar subcategorías</button>
        </form>
    @endif
</section>
@endsection