@extends('layouts.admin')
@section('titulo', 'Nueva subcategoría — Delivery Patacamaya')
@section('contenido-admin')
<div class="page-heading"><h1 class="h2">Nueva subcategoría</h1><p class="text-secondary">Añade una clasificación dentro de una categoría general.</p></div>
<form class="card soft-card p-3 p-sm-4" method="POST" action="{{ route('administrador.subcategorias.store') }}">@include('admin.subcategorias._form')</form>
@endsection
