@extends('layouts.negocio')
@section('titulo', 'Editar producto — Delivery Patacamaya')
@section('contenido-negocio')<div class="page-heading"><h1 class="h2">Editar producto</h1><p class="text-secondary">Actualiza los datos, la imagen o disponibilidad.</p></div><form class="card soft-card p-3 p-sm-4" method="POST" enctype="multipart/form-data" action="{{ route('negocio.productos.update', [$negocio, $producto]) }}">@include('negocio.productos._form')</form>@endsection
