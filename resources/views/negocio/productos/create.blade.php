@extends('layouts.negocio')
@section('titulo', 'Nuevo producto — Delivery Patacamaya')
@section('contenido-negocio')<div class="page-heading"><h1 class="h2">Nuevo producto</h1><p class="text-secondary">Añade un artículo al catálogo de {{ $negocio->nombre }}.</p></div><form class="card soft-card p-3 p-sm-4" method="POST" enctype="multipart/form-data" action="{{ route('negocio.productos.store', $negocio) }}">@include('negocio.productos._form')</form>@endsection
