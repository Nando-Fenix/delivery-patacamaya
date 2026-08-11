<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCategoriaProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $negocio = $this->route('negocio');

        return ['nombre' => ['required', 'string', 'max:100', Rule::unique('categorias_producto')->where(fn ($q) => $q->where('negocio_id', $negocio->id))->ignore($this->route('categoriaProducto'))], 'descripcion' => ['nullable', 'string', 'max:255'], 'activo' => ['required', 'boolean'], 'orden' => ['nullable', 'integer', 'min:0']];
    }
}
