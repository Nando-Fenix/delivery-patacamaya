<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarSubcategoriasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $negocio = $this->route('negocio');

        return [
            'subcategorias' => ['sometimes', 'array'],
            'subcategorias.*' => [
                'integer',
                'distinct',
                Rule::exists('subcategorias_negocio', 'id')->where(
                    fn ($consulta) => $consulta
                        ->where('categoria_negocio_id', $negocio->categoria_negocio_id)
                        ->where('activo', true)
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return ['subcategorias.*.exists' => 'Una de las subcategorías no está disponible para la categoría de este negocio.'];
    }
}
