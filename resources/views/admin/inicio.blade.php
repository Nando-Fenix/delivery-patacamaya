@extends('layouts.admin')

@section('titulo', 'Dashboard administrativo — Delivery Patacamaya')

@section('contenido-admin')
@if ($sinZonasActivas)<div class="alert alert-warning"><i class="bi bi-info-circle me-2"></i>Aún no se configuraron zonas de entrega. <a href="{{ route('administrador.zonas.create') }}">Crear zona</a></div>@endif
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">Resumen general de Delivery Patacamaya.</p>
    </div>
</div>

@php
    $tarjetas = [
        ['titulo' => 'Total de negocios', 'valor' => $metricas['negocios'], 'icono' => 'bi-shop'],
        ['titulo' => 'Pendientes', 'valor' => $metricas['pendientes'], 'icono' => 'bi-hourglass-split'],
        ['titulo' => 'Aprobados', 'valor' => $metricas['aprobados'], 'icono' => 'bi-patch-check'],
        ['titulo' => 'Categorías activas', 'valor' => $metricas['categoriasActivas'], 'icono' => 'bi-tags'],
        ['titulo' => 'Usuarios registrados', 'valor' => $metricas['usuarios'], 'icono' => 'bi-people'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($tarjetas as $tarjeta)
        <div class="col-12 col-sm-6 col-xl-4">
            <article class="card metric-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="metric-icon"><i class="bi {{ $tarjeta['icono'] }}"></i></span>
                    <div>
                        <div class="fs-3 fw-bold lh-1">{{ $tarjeta['valor'] }}</div>
                        <div class="text-secondary mt-1">{{ $tarjeta['titulo'] }}</div>
                    </div>
                </div>
            </article>
        </div>
    @endforeach
</div>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Accesos rápidos</h2>
        <div class="d-grid d-sm-flex gap-2">
            <a class="btn btn-primary" href="{{ route('administrador.categorias.index') }}"><i class="bi bi-tags me-2"></i>Administrar categorías</a>
            <a class="btn btn-outline-success" href="{{ route('administrador.negocios.index') }}"><i class="bi bi-shop me-2"></i>Revisar negocios</a>
        </div>
    </div>
</section>
@endsection
