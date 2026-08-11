@extends('layouts.negocio')
@section('titulo', 'Nueva categoría — Delivery Patacamaya')
@section('contenido-negocio')<div class="page-heading"><h1 class="h2">Nueva categoría</h1><p class="text-secondary">Agrupa productos relacionados dentro de tu catálogo.</p></div><form class="card soft-card p-3 p-sm-4" method="POST" action="{{ route('negocio.categorias-producto.store', $negocio) }}">@include('negocio.categorias-producto._form')</form>@endsection
