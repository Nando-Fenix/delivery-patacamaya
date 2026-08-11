<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['estado' => ['required', Rule::in(['aceptado', 'rechazado', 'en_preparacion', 'listo'])], 'motivo_rechazo' => ['nullable', 'string', 'max:300', 'required_if:estado,rechazado']];
    }
}
