@extends('layouts.admin')
@section('titulo', 'Editar subcategoría — Delivery Patacamaya')
@section('contenido-admin')
<div class="page-heading"><h1 class="h2">Editar subcategoría</h1><p class="text-secondary">Actualiza su categoría, nombre y estado.</p></div>
<form class="card soft-card p-3 p-sm-4" method="POST" action="{{ route('administrador.subcategorias.update', $subcategoria) }}">@include('admin.subcategorias._form')</form>
@endsection
