<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Negocio\ActualizarNegocioRequest;
use App\Http\Requests\Negocio\ActualizarSubcategoriasRequest;
use App\Models\Negocio;
use App\Models\SubcategoriaNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MiNegocioController extends Controller
{
    public function edit(Negocio $negocio): View
    {
        $negocio->load(['categoria', 'subcategorias']);
        $subcategoriasDisponibles = SubcategoriaNegocio::query()
            ->where('categoria_negocio_id', $negocio->categoria_negocio_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('negocio.mi-negocio', compact('negocio', 'subcategoriasDisponibles'));
    }

    public function update(ActualizarNegocioRequest $request, Negocio $negocio): RedirectResponse
    {
        $negocio->update($request->validated());

        return back()->with('estado', 'Información del negocio actualizada.');
    }

    public function actualizarSubcategorias(ActualizarSubcategoriasRequest $request, Negocio $negocio): RedirectResponse
    {
        $negocio->subcategorias()->sync($request->validated('subcategorias', []));

        return back()->with('estado', 'Subcategorías actualizadas correctamente.');
    }
}
