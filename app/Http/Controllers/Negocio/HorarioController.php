<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Requests\Negocio\GuardarHorariosRequest;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HorarioController
{
    private const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    public function edit(Negocio $negocio): View
    {
        $existentes = $negocio->horarios->keyBy('dia_semana');

        return view('negocio.horarios', ['negocio' => $negocio, 'dias' => self::DIAS, 'existentes' => $existentes]);
    }

    public function update(GuardarHorariosRequest $r, Negocio $negocio): RedirectResponse
    {
        foreach ($r->validated('horarios') as $h) {
            $cerrado = (bool) ($h['cerrado'] ?? false);
            $negocio->horarios()->updateOrCreate(['dia_semana' => $h['dia_semana']], ['cerrado' => $cerrado, 'hora_apertura' => $cerrado ? null : $h['hora_apertura'], 'hora_cierre' => $cerrado ? null : $h['hora_cierre']]);
        }

return back()->with('estado', 'Horarios guardados correctamente.');
    }
}
