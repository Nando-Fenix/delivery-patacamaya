<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Negocio\ActualizarUbicacionRequest;
use App\Models\Negocio;
use App\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    public function edit(Negocio $negocio): View
    {
        return view('negocio.ubicacion', ['negocio' => $negocio, 'zonas' => Zona::where('activo', true)->orderBy('nombre')->get()]);
    }

    public function update(ActualizarUbicacionRequest $r, Negocio $negocio): RedirectResponse
    {
        $negocio->update($r->validated());

        return back()->with('estado', 'Ubicación actualizada.');
    }
}
