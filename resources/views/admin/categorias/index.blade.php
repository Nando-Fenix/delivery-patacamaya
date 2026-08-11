@extends('layouts.admin')
@section('titulo', 'Categorías — Delivery Patacamaya')
@section('contenido-admin')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div><h1 class="h2 mb-1">Categorías</h1><p class="text-secondary mb-0">Organiza los tipos de negocio disponibles.</p></div>
    <a class="btn btn-primary" href="{{ route('administrador.categorias.create') }}"><i class="bi bi-plus-lg me-1"></i>Nueva categoría</a>
</div>
<form class="card border-0 shadow-sm mb-3" method="GET">
    <div class="card-body d-flex flex-column flex-sm-row gap-2">
        <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" name="buscar" value="{{ $busqueda }}" placeholder="Buscar por nombre" aria-label="Buscar categoría"></div>
        <button class="btn btn-outline-success" type="submit">Buscar</button>
        @if ($busqueda)<a class="btn btn-light" href="{{ route('administrador.categorias.index') }}">Limpiar</a>@endif
    </div>
</form>
<section class="card border-0 shadow-sm overflow-hidden d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Categoría</th><th>Estado</th><th>Negocios</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse ($categorias as $categoria)
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="metric-icon"><i class="bi {{ $categoria->icono ?: 'bi-tag' }}"></i></span><div><div class="fw-semibold">{{ $categoria->nombre }}</div><small class="text-secondary">{{ $categoria->descripcion ?: 'Sin descripción' }}</small></div></div></td>
                    <td><span class="badge {{ $categoria->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</span></td>
                    <td>{{ $categoria->negocios_count }}</td>
                    <td class="text-end"><div class="d-inline-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="{{ route('administrador.categorias.edit', $categoria) }}" aria-label="Editar {{ $categoria->nombre }}"><i class="bi bi-pencil"></i></a><form class="requiere-confirmacion" method="POST" action="{{ route('administrador.categorias.estado', $categoria) }}" data-mensaje="¿Deseas {{ $categoria->activo ? 'desactivar' : 'activar' }} la categoría {{ $categoria->nombre }}?">@csrf @method('PATCH')<button class="btn btn-sm {{ $categoria->activo ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit"><i class="bi {{ $categoria->activo ? 'bi-pause-circle' : 'bi-play-circle' }}"></i></button></form></div></td>
                </tr>
            @empty
                <tr><td class="text-center text-secondary py-5" colspan="4"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No se encontraron categorías.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($categorias->hasPages())<div class="card-footer bg-white">{{ $categorias->links() }}</div>@endif
</section>
<div class="d-md-none d-grid gap-3">
@forelse ($categorias as $categoria)
    <article class="card soft-card p-3"><div class="d-flex justify-content-between gap-2"><div class="d-flex gap-2"><span class="metric-icon"><i class="bi {{ $categoria->icono ?: 'bi-tag' }}"></i></span><div><h2 class="h5 mb-1">{{ $categoria->nombre }}</h2><small class="text-secondary">{{ $categoria->descripcion ?: 'Sin descripción' }}</small></div></div><span class="badge align-self-start {{ $categoria->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</span></div><div class="d-flex justify-content-between align-items-center mt-3"><small>{{ $categoria->negocios_count }} negocios</small><div class="d-flex gap-2"><a class="btn btn-outline-primary touch-target" href="{{ route('administrador.categorias.edit', $categoria) }}"><i class="bi bi-pencil me-1"></i>Editar</a><form class="requiere-confirmacion" method="POST" action="{{ route('administrador.categorias.estado', $categoria) }}" data-mensaje="¿Deseas {{ $categoria->activo ? 'desactivar' : 'activar' }} la categoría {{ $categoria->nombre }}?">@csrf @method('PATCH')<button class="btn touch-target {{ $categoria->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"><i class="bi {{ $categoria->activo ? 'bi-pause-circle' : 'bi-play-circle' }}"></i></button></form></div></div></article>
@empty <div class="empty-state">No se encontraron categorías.</div> @endforelse
</div>
@endsection
