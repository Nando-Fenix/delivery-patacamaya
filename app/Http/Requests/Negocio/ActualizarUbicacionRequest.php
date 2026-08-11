<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['zona_id' => ['required', 'integer', Rule::exists('zonas', 'id')->where('activo', true)], 'direccion_referencia' => ['required', 'string', 'max:255'], 'latitud' => ['required', 'numeric', 'between:-90,90'], 'longitud' => ['required', 'numeric', 'between:-180,180']];
    }
}
