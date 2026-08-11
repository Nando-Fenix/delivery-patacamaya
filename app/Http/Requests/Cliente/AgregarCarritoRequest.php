<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class AgregarCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['cantidad' => ['required', 'integer', 'between:1,99'], 'reemplazar' => ['sometimes', 'boolean']];
    }
}
