@extends('layouts.admin')

@section('titulo', 'Clasificar negocio — Delivery Patacamaya')

@section('contenido-admin')
@php
    $seleccionadas = old('subcategorias', $negocio->subcategorias->modelKeys());
@endphp
<div class="page-heading">
    <a href="{{ route('administrador.negocios.show', $negocio) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
    <h1 class="h2 mt-2">Clasificar {{ $negocio->nombre }}</h1>
    <p class="text-secondary">Selecciona su categoría general y una o varias subcategorías compatibles.</p>
</div>
<form class="card soft-card p-3 p-sm-4" method="POST" action="{{ route('administrador.negocios.clasificacion.update', $negocio) }}">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="form-label" for="categoria_negocio_id">Categoría general</label>
        <select class="form-select @error('categoria_negocio_id') is-invalid @enderror" id="categoria_negocio_id" name="categoria_negocio_id" required>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_negocio_id', $negocio->categoria_negocio_id) == $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>
    <fieldset>
        <legend class="h5">Subcategorías</legend>
        <p class="small text-secondary">Solo se muestran opciones de la categoría elegida.</p>
        @error('subcategorias')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <div class="row g-2" id="opcionesSubcategorias">
            @foreach ($categorias as $categoria)
                @foreach ($categoria->subcategorias as $subcategoria)
                    <div class="col-12 col-sm-6 opcion-subcategoria" data-categoria="{{ $categoria->id }}">
                        <label class="card selection-card p-3">
                            <span class="form-check">
                                <input class="form-check-input" type="checkbox" name="subcategorias[]" value="{{ $subcategoria->id }}" {{ in_array($subcategoria->id, $seleccionadas) ? 'checked' : '' }}>
                                <span class="form-check-label ms-1">{{ $subcategoria->nombre }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            @endforeach
        </div>
    </fieldset>
    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
        <a class="btn btn-light order-2 order-sm-1" href="{{ route('administrador.negocios.show', $negocio) }}">Cancelar</a>
        <button class="btn btn-primary touch-button order-1 order-sm-2">Guardar clasificación</button>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const categoria = document.getElementById('categoria_negocio_id');
    const actualizar = () => document.querySelectorAll('.opcion-subcategoria').forEach((opcion) => {
        const visible = opcion.dataset.categoria === categoria.value;
        opcion.classList.toggle('d-none', ! visible);
        if (! visible) opcion.querySelector('input').checked = false;
    });
    categoria.addEventListener('change', actualizar);
    actualizar();
});
</script>
@endsection
