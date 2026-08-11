<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Negocio\GuardarProductoRequest;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    private function propio(Negocio $n, Producto $p): void
    {
        abort_unless($p->negocio_id === $n->id, 403);
    }

    public function index(Request $r, Negocio $negocio): View
    {
        $f = $r->validate(['buscar' => ['nullable', 'string', 'max:150'], 'categoria' => ['nullable', 'integer'], 'activo' => ['nullable', Rule::in(['0', '1'])], 'disponible' => ['nullable', Rule::in(['0', '1'])]]);
        $q = $negocio->productos()->with('categoria')->when($f['buscar'] ?? null, fn ($q, $v) => $q->where('nombre', 'like', "%{$v}%"))->when($f['categoria'] ?? null, fn ($q, $v) => $q->where('categoria_producto_id', $v))->when(array_key_exists('activo', $f), fn ($q) => $q->where('activo', (bool) $f['activo']))->when(array_key_exists('disponible', $f), fn ($q) => $q->where('disponible', (bool) $f['disponible']))->orderByRaw('orden is null')->orderBy('orden')->latest();

        return view('negocio.productos.index', ['negocio' => $negocio, 'productos' => $q->paginate(12)->withQueryString(), 'categorias' => $negocio->categoriasProducto()->orderBy('nombre')->get(), 'filtros' => $f]);
    }

    public function create(Negocio $negocio): View
    {
        return view('negocio.productos.create', ['negocio' => $negocio, 'categorias' => $negocio->categoriasProducto()->orderBy('nombre')->get()]);
    }

    public function store(GuardarProductoRequest $r, Negocio $negocio): RedirectResponse
    {
        $d = $r->validated();
        if ($r->hasFile('imagen')) {
            $d['imagen'] = $r->file('imagen')->store('productos', 'public');
        }$negocio->productos()->create($d);

        return redirect()->route('negocio.productos.index', $negocio)->with('estado', 'Producto creado.');
    }

    public function edit(Negocio $negocio, Producto $producto): View
    {
        $this->propio($negocio, $producto);

        return view('negocio.productos.edit', ['negocio' => $negocio, 'producto' => $producto, 'categorias' => $negocio->categoriasProducto()->orderBy('nombre')->get()]);
    }

    public function update(GuardarProductoRequest $r, Negocio $negocio, Producto $producto): RedirectResponse
    {
        $this->propio($negocio, $producto);
        $d = $r->validated();
        if ($r->hasFile('imagen')) {
            $anterior = $producto->imagen;
            $d['imagen'] = $r->file('imagen')->store('productos', 'public');
            $producto->update($d);
            if ($anterior) {
                Storage::disk('public')->delete($anterior);
            }
        } else {
            $producto->update($d);
        }

return redirect()->route('negocio.productos.index', $negocio)->with('estado', 'Producto actualizado.');
    }

    public function estado(Negocio $negocio, Producto $producto): RedirectResponse
    {
        $this->propio($negocio, $producto);
        $producto->update(['activo' => ! $producto->activo]);

        return back()->with('estado', 'Estado del producto actualizado.');
    }

    public function disponibilidad(Negocio $negocio, Producto $producto): RedirectResponse
    {
        $this->propio($negocio, $producto);
        $producto->update(['disponible' => ! $producto->disponible]);

        return back()->with('estado','Disponibilidad actualizada.');
    }
}
