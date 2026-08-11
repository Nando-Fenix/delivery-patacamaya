@extends('layouts.admin')
@section('titulo', 'Negocios — Delivery Patacamaya')
@section('contenido-admin')
<div class="mb-4"><h1 class="h2 mb-1">Negocios</h1><p class="text-secondary mb-0">Revisa, aprueba y administra los negocios registrados.</p></div>

<form class="card border-0 shadow-sm mb-3" method="GET">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-lg-4"><label class="visually-hidden" for="buscar">Buscar</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Negocio o propietario"></div></div>
            <div class="col-12 col-sm-4 col-lg-3"><label class="visually-hidden" for="categoria">Categoría</label><select class="form-select" id="categoria" name="categoria"><option value="">Todas las categorías</option>@foreach ($categorias as $categoria)<option value="{{ $categoria->id }}" @selected(($filtros['categoria'] ?? '') == $categoria->id)>{{ $categoria->nombre }}</option>@endforeach</select></div>
            <div class="col-6 col-sm-4 col-lg-2"><label class="visually-hidden" for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="">Todo estado</option>@foreach (['pendiente', 'aprobado', 'rechazado'] as $estado)<option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
            <div class="col-6 col-sm-4 col-lg-2"><label class="visually-hidden" for="activo">Actividad</label><select class="form-select" id="activo" name="activo"><option value="">Todos</option><option value="1" @selected(($filtros['activo'] ?? '') === '1')>Activos</option><option value="0" @selected(($filtros['activo'] ?? '') === '0')>Inactivos</option></select></div>
            <div class="col-12 col-lg-1 d-grid"><button class="btn btn-outline-success" type="submit" aria-label="Aplicar filtros"><i class="bi bi-funnel"></i></button></div>
        </div>
        @if (array_filter($filtros, fn ($valor) => $valor !== null && $valor !== ''))<div class="mt-2"><a class="small" href="{{ route('administrador.negocios.index') }}">Limpiar filtros</a></div>@endif
    </div>
</form>

<section class="card border-0 shadow-sm overflow-hidden d-none d-lg-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Negocio</th><th>Propietario</th><th>Categoría</th><th>Estado</th><th>Actividad</th><th>Registro</th><th></th></tr></thead>
            <tbody>
            @forelse ($negocios as $negocio)
                <tr>
                    <td><div class="fw-semibold">{{ $negocio->nombre }}</div><small class="text-secondary">{{ $negocio->telefono }}</small></td>
                    <td>{{ $negocio->usuario->nombres }} {{ $negocio->usuario->apellidos }}</td>
                    <td>{{ $negocio->categoria->nombre }}</td>
                    <td><span class="badge {{ match($negocio->estado) { 'aprobado' => 'text-bg-success', 'rechazado' => 'text-bg-danger', default => 'text-bg-warning' } }}">{{ ucfirst($negocio->estado) }}</span></td>
                    <td><span class="badge {{ $negocio->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $negocio->activo ? 'Activo' : 'Inactivo' }}</span></td>
                    <td class="text-nowrap">{{ $negocio->created_at->format('d/m/Y') }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('administrador.negocios.show', $negocio) }}"><i class="bi bi-eye me-1"></i>Ver</a></td>
                </tr>
            @empty
                <tr><td class="text-center text-secondary py-5" colspan="7"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No se encontraron negocios.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($negocios->hasPages())<div class="card-footer bg-white">{{ $negocios->links() }}</div>@endif
</section>
<div class="d-lg-none d-grid gap-3">
@forelse($negocios as $negocio)
    <article class="card soft-card p-3"><div class="d-flex justify-content-between gap-2"><div><h2 class="h5 mb-1">{{ $negocio->nombre }}</h2><div class="small text-secondary">{{ $negocio->categoria->nombre }}</div></div><span class="badge align-self-start {{ match($negocio->estado) { 'aprobado' => 'text-bg-success', 'rechazado' => 'text-bg-danger', default => 'text-bg-warning' } }}">{{ ucfirst($negocio->estado) }}</span></div><div class="small mt-3"><div><i class="bi bi-person me-2"></i>{{ $negocio->usuario->nombres }} {{ $negocio->usuario->apellidos }}</div><div class="mt-1"><i class="bi bi-tags me-2"></i>{{ $negocio->subcategorias->pluck('nombre')->join(', ') ?: 'Sin subcategorías' }}</div></div><div class="d-flex justify-content-between align-items-center mt-3"><span class="badge {{ $negocio->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $negocio->activo ? 'Activo' : 'Inactivo' }}</span><a class="btn btn-outline-primary touch-target" href="{{ route('administrador.negocios.show', $negocio) }}"><i class="bi bi-eye me-1"></i>Ver detalle</a></div></article>
@empty <div class="empty-state">No se encontraron negocios.</div> @endforelse
</div>
@endsection
