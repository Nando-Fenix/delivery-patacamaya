<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GuardarZonaRequest;
use App\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZonaController extends Controller
{
    public function index(Request $r): View
    {
        $buscar = trim($r->string('buscar'));
        $zonas = Zona::withCount(['negocios', 'direcciones'])->when($buscar, fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))->orderByRaw('orden is null')->orderBy('orden')->paginate(10)->withQueryString();

        return view('admin.zonas.index', compact('zonas', 'buscar'));
    }

    public function create(): View
    {
        return view('admin.zonas.form', ['zona' => new Zona]);
    }

    public function store(GuardarZonaRequest $r): RedirectResponse
    {
        Zona::create($r->validated());

        return redirect()->route('administrador.zonas.index')->with('estado', 'Zona creada.');
    }

    public function edit(Zona $zona): View
    {
        return view('admin.zonas.form', compact('zona'));
    }

    public function update(GuardarZonaRequest $r, Zona $zona): RedirectResponse
    {
        $zona->update($r->validated());

        return redirect()->route('administrador.zonas.index')->with('estado', 'Zona actualizada.');
    }

    public function estado(Zona $zona): RedirectResponse
    {
        $zona->update(['activo' => ! $zona->activo]);

        return back()->with('estado','Estado de zona actualizado.');
    }
}
