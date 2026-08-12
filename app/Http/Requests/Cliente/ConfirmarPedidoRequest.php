<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['direccion_id' => ['required', 'integer'], 'metodo_pago' => ['required', Rule::in(['efectivo', 'qr'])], 'observaciones' => ['nullable', 'string', 'max:500']];
    }

    public function messages(): array
    {
        return [
            'direccion_id.required' => 'Debes seleccionar una dirección de entrega.',
            'direccion_id.integer' => 'La dirección seleccionada no es válida.',
            'direccion_id.exists' => 'La dirección seleccionada no es válida.',

            'metodo_pago.required' => 'Debes seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',

            'observaciones.max' => 'Las observaciones no pueden superar los 500 caracteres.',
        ];
    }
}
