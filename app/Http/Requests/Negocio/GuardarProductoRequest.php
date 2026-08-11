<?php

namespace App\Http\Requests\Negocio;

use App\Models\CategoriaProducto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_producto_id' => ['nullable', 'integer', 'exists:categorias_producto,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'precio' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'activo' => ['required', 'boolean'],
            'disponible' => ['required', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $categoriaId = $this->input('categoria_producto_id');
            if ($categoriaId && CategoriaProducto::whereKey($categoriaId)->where('negocio_id', '!=', $this->route('negocio')->id)->exists()) {
                $validator->errors()->add('categoria_producto_id', 'La categoría no pertenece a este negocio.');
            }
        }];
    }
}
