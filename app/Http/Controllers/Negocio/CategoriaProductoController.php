<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Negocio\GuardarCategoriaProductoRequest;
use App\Models\CategoriaProducto;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaProductoController extends Controller
{
    public function index(Negocio $negocio): View
    {
        return view('negocio.categorias-producto.index', [
            'negocio' => $negocio,
            'categorias' => $negocio->categoriasProducto()->withCount('productos')->orderByRaw('orden is null')->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    public function create(Negocio $negocio): View
    {
        return view('negocio.categorias-producto.create', compact('negocio'));
    }

    public function store(GuardarCategoriaProductoRequest $request, Negocio $negocio): RedirectResponse
    {
        $negocio->categoriasProducto()->create($request->validated());

        return redirect()->route('negocio.categorias-producto.index', $negocio)->with('estado', 'Categoría creada.');
    }

    public function edit(Negocio $negocio, CategoriaProducto $categoriaProducto): View
    {
        $this->comprobarPropiedad($negocio, $categoriaProducto);

        return view('negocio.categorias-producto.edit', compact('negocio', 'categoriaProducto'));
    }

    public function update(GuardarCategoriaProductoRequest $request, Negocio $negocio, CategoriaProducto $categoriaProducto): RedirectResponse
    {
        $this->comprobarPropiedad($negocio, $categoriaProducto);
        $categoriaProducto->update($request->validated());

        return redirect()->route('negocio.categorias-producto.index', $negocio)->with('estado', 'Categoría actualizada.');
    }

    public function estado(Negocio $negocio, CategoriaProducto $categoriaProducto): RedirectResponse
    {
        $this->comprobarPropiedad($negocio, $categoriaProducto);
        $categoriaProducto->update(['activo' => ! $categoriaProducto->activo]);

        return back()->with('estado', 'Estado de categoría actualizado.');
    }

    private function comprobarPropiedad(Negocio $negocio, CategoriaProducto $categoriaProducto): void
    {
        abort_unless($categoriaProducto->negocio_id === $negocio->id, 403);
    }
}
