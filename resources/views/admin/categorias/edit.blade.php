@extends('layouts.admin')
@section('titulo', 'Editar categoría — Delivery Patacamaya')
@section('contenido-admin')
<div class="mb-4"><h1 class="h2 mb-1">Editar categoría</h1><p class="text-secondary mb-0">Actualiza la información sin afectar sus negocios relacionados.</p></div>
<section class="card border-0 shadow-sm"><form class="card-body p-4" method="POST" action="{{ route('administrador.categorias.update', $categoria) }}">@include('admin.categorias._form')</form></section>
@endsection
