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
}
