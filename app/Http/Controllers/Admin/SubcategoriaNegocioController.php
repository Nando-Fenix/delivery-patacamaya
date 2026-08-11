<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GuardarSubcategoriaNegocioRequest;
use App\Models\CategoriaNegocio;
use App\Models\SubcategoriaNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubcategoriaNegocioController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer', 'exists:categorias_negocio,id'],
            'activo' => ['nullable', Rule::in(['1', '0'])],
        ]);
        $subcategorias = SubcategoriaNegocio::with('categoria')->withCount('negocios')
            ->when($filtros['buscar'] ?? null, fn ($q, $v) => $q->where('nombre', 'like', "%{$v}%"))
            ->when($filtros['categoria'] ?? null, fn ($q, $v) => $q->where('categoria_negocio_id', $v))
            ->when(array_key_exists('activo', $filtros), fn ($q) => $q->where('activo', (bool) $filtros['activo']))
            ->orderBy('nombre')->paginate(12)->withQueryString();

        return view('admin.subcategorias.index', ['subcategorias' => $subcategorias, 'categorias' => CategoriaNegocio::orderBy('nombre')->get(), 'filtros' => $filtros]);
    }

    public function create(): View
    {
        return view('admin.subcategorias.create', ['categorias' => CategoriaNegocio::orderBy('nombre')->get()]);
    }

    public function store(GuardarSubcategoriaNegocioRequest $request): RedirectResponse
    {
        SubcategoriaNegocio::create($request->validated());

        return redirect()->route('administrador.subcategorias.index')->with('estado', 'Subcategoría creada correctamente.');
    }

    public function edit(SubcategoriaNegocio $subcategoria): View
    {
        return view('admin.subcategorias.edit', ['subcategoria' => $subcategoria, 'categorias' => CategoriaNegocio::orderBy('nombre')->get()]);
    }

    public function update(GuardarSubcategoriaNegocioRequest $request, SubcategoriaNegocio $subcategoria): RedirectResponse
    {
        $subcategoria->update($request->validated());

        return redirect()->route('administrador.subcategorias.index')->with('estado', 'Subcategoría actualizada correctamente.');
    }

    public function cambiarEstado(SubcategoriaNegocio $subcategoria): RedirectResponse
    {
        $subcategoria->update(['activo' => ! $subcategoria->activo]);

        return back()->with('estado', 'Subcategoría '.($subcategoria->activo ? 'activada' : 'desactivada').' correctamente.');
    }
}
