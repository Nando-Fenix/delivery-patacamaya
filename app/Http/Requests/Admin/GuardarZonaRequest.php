<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarZonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:100', Rule::unique('zonas')->ignore($this->route('zona'))], 'descripcion' => ['nullable', 'string', 'max:255'], 'activo' => ['required', 'boolean'], 'orden' => ['nullable', 'integer', 'min:0']];
    }
}
