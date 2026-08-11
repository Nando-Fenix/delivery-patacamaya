<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarHorariosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['horarios' => ['required', 'array', 'size:7'], 'horarios.*.dia_semana' => ['required', 'distinct', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'], 'horarios.*.cerrado' => ['nullable', 'boolean'], 'horarios.*.hora_apertura' => ['nullable', 'date_format:H:i'], 'horarios.*.hora_cierre' => ['nullable', 'date_format:H:i']];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            foreach ($this->input('horarios', []) as $i => $h) {
                if (! ($h['cerrado'] ?? false)) {
                    if (empty($h['hora_apertura']) || empty($h['hora_cierre'])) {
                        $v->errors()->add("horarios.$i.hora_apertura", 'Apertura y cierre son obligatorios.');
                    } elseif ($h['hora_cierre'] <= $h['hora_apertura']) {
                        $v->errors()->add("horarios.$i.hora_cierre", 'La hora de cierre debe ser posterior a la apertura.');
                    }
                }
            }
        }];
    }
}
