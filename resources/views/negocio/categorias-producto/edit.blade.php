@extends('layouts.negocio')
@section('titulo', 'Editar categoría — Delivery Patacamaya')
@section('contenido-negocio')<div class="page-heading"><h1 class="h2">Editar categoría</h1><p class="text-secondary">Actualiza la organización de tu catálogo.</p></div><form class="card soft-card p-3 p-sm-4" method="POST" action="{{ route('negocio.categorias-producto.update', [$negocio, $categoriaProducto]) }}">@include('negocio.categorias-producto._form')</form>@endsection
