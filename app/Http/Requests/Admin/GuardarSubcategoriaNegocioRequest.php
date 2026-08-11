<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarSubcategoriaNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_negocio_id' => ['required', 'integer', 'exists:categorias_negocio,id'],
            'nombre' => ['required', 'string', 'max:100', Rule::unique('subcategorias_negocio')->where(fn ($query) => $query->where('categoria_negocio_id', $this->integer('categoria_negocio_id')))->ignore($this->route('subcategoria'))],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'icono' => ['nullable', 'string', 'max:100', 'regex:/^bi-[a-z0-9-]+$/'],
            'activo' => ['required', 'boolean'],
        ];
    }
}
