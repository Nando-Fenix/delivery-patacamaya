<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarDireccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:100'], 'zona_id' => ['required', 'integer', Rule::exists('zonas', 'id')->where('activo', true)], 'direccion_referencia' => ['required', 'string', 'max:255'], 'latitud' => ['required', 'numeric', 'between:-90,90'], 'longitud' => ['required', 'numeric', 'between:-180,180'], 'predeterminada' => ['required', 'boolean']];
    }
}
