<?php

namespace App\Http\Requests\Admin;

use App\Models\SubcategoriaNegocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ActualizarClasificacionNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_negocio_id' => ['required', 'integer', 'exists:categorias_negocio,id'],
            'subcategorias' => ['nullable', 'array'],
            'subcategorias.*' => ['integer', 'distinct', 'exists:subcategorias_negocio,id'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $ids = $this->input('subcategorias', []);
            if ($ids && SubcategoriaNegocio::whereIn('id', $ids)->where('categoria_negocio_id', '!=', $this->integer('categoria_negocio_id'))->exists()) {
                $validator->errors()->add('subcategorias', 'Todas las subcategorías deben pertenecer a la categoría seleccionada.');
            }
        }];
    }
}
