@csrf
@isset($categoria) @method('PUT') @endisset

<div class="mb-3">
    <label class="form-label" for="nombre">Nombre <span class="text-danger">*</span></label>
    <input class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre ?? '') }}" maxlength="100" required>
    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="descripcion">Descripción</label>
    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3" maxlength="255">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="icono">Clase de Bootstrap Icon</label>
    <div class="input-group">
        <span class="input-group-text"><i class="bi {{ old('icono', $categoria->icono ?? 'bi-tag') }}"></i></span>
        <input class="form-control @error('icono') is-invalid @enderror" id="icono" name="icono" value="{{ old('icono', $categoria->icono ?? '') }}" placeholder="bi-shop" maxlength="100">
        @error('icono')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-text">Ejemplos: bi-book, bi-shop, bi-tools.</div>
</div>
<div class="form-check form-switch mb-4">
    <input type="hidden" name="activo" value="0">
    <input class="form-check-input" id="activo" name="activo" type="checkbox" value="1" @checked(old('activo', $categoria->activo ?? true))>
    <label class="form-check-label" for="activo">Categoría activa</label>
</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
    <a class="btn btn-light" href="{{ route('administrador.categorias.index') }}">Cancelar</a>
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Guardar categoría</button>
</div>
