<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GuardarCategoriaNegocioRequest;
use App\Models\CategoriaNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaNegocioController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim($request->string('buscar')->toString());
        $categorias = CategoriaNegocio::query()
            ->withCount('negocios')
            ->when($busqueda, fn ($query) => $query->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categorias.index', compact('categorias', 'busqueda'));
    }

    public function create(): View
    {
        return view('admin.categorias.create');
    }

    public function store(GuardarCategoriaNegocioRequest $request): RedirectResponse
    {
        CategoriaNegocio::create($request->validated());

        return redirect()->route('administrador.categorias.index')->with('estado', 'Categoría creada correctamente.');
    }

    public function edit(CategoriaNegocio $categoria): View
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(GuardarCategoriaNegocioRequest $request, CategoriaNegocio $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('administrador.categorias.index')->with('estado', 'Categoría actualizada correctamente.');
    }

    public function cambiarEstado(CategoriaNegocio $categoria): RedirectResponse
    {
        $categoria->update(['activo' => ! $categoria->activo]);
        $accion = $categoria->activo ? 'activada' : 'desactivada';

        return back()->with('estado', "Categoría {$accion} correctamente.");
    }
}
