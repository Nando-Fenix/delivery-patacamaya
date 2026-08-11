<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:150'], 'descripcion' => ['nullable', 'string', 'max:1000'], 'telefono' => ['required', 'string', 'max:20'], 'direccion_referencia' => ['nullable', 'string', 'max:255']];
    }
}
