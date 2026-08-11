@extends('layouts.admin')
@section('titulo', 'Nueva categoría — Delivery Patacamaya')
@section('contenido-admin')
<div class="mb-4"><h1 class="h2 mb-1">Nueva categoría</h1><p class="text-secondary mb-0">Crea una categoría para organizar los negocios.</p></div>
<section class="card border-0 shadow-sm"><form class="card-body p-4" method="POST" action="{{ route('administrador.categorias.store') }}">@include('admin.categorias._form')</form></section>
@endsection
