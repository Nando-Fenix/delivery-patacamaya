@extends('layouts.app')

@section('titulo', 'Inicio — ' . ucfirst($rol))

@section('contenido')
<section class="card role-card">
    <div class="card-body p-4 p-md-5">
        <span class="badge rounded-pill text-bg-success mb-3">{{ ucfirst($rol) }}</span>
        <h1 class="h2">Hola, {{ auth()->user()->nombres }}</h1>
        <p class="lead text-secondary mb-4">Ingresaste correctamente al espacio de <strong>{{ $rol }}</strong>.</p>
        <div class="alert alert-light border mb-0">
            <i class="bi bi-tools me-2 text-success"></i>
            Este panel está preparado para la siguiente fase del MVP.
        </div>
    </div>
</section>
@endsection
