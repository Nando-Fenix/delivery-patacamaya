<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\SubcategoriaNegocio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExplorarController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'categoria' => ['nullable', 'integer', Rule::exists('categorias_negocio', 'id')->where('activo', true)],
            'subcategoria' => ['nullable', 'integer', Rule::exists('subcategorias_negocio', 'id')->where('activo', true)],
        ]);
        $categorias = CategoriaNegocio::query()->where('activo', true)->with(['subcategorias' => fn ($q) => $q->where('activo', true)->orderBy('nombre')])->orderBy('nombre')->get();
        $negocios = Negocio::query()->visiblesParaCliente()->with(['categoria', 'subcategorias' => fn ($q) => $q->where('activo', true), 'horarios'])
            ->when($filtros['buscar'] ?? null, fn ($q, $valor) => $q->where(fn ($c) => $c->where('nombre', 'like', "%{$valor}%")->orWhere('descripcion', 'like', "%{$valor}%")))
            ->when($filtros['categoria'] ?? null, fn ($q, $id) => $q->where('categoria_negocio_id', $id))
            ->when($filtros['subcategoria'] ?? null, fn ($q, $id) => $q->whereHas('subcategorias', fn ($s) => $s->whereKey($id)->where('activo', true)))
            ->orderBy('nombre')->paginate(12)->withQueryString();
        $subcategorias = isset($filtros['categoria']) ? SubcategoriaNegocio::query()->where('categoria_negocio_id', $filtros['categoria'])->where('activo', true)->orderBy('nombre')->get() : collect();

        return view('cliente.inicio', compact('categorias', 'subcategorias', 'negocios', 'filtros'));
    }

    public function show(Request $request, Negocio $negocio): View
    {
        abort_unless($negocio->estado === 'aprobado' && $negocio->activo && $negocio->categoria()->where('activo', true)->exists(), 404);
        $filtros = $request->validate(['producto' => ['nullable', 'string', 'max:150'], 'categoria_producto' => ['nullable', 'integer']]);
        $negocio->load(['zona', 'categoria', 'subcategorias' => fn ($q) => $q->where('activo', true), 'horarios' => fn ($q) => $q->orderByRaw("CASE dia_semana WHEN 'lunes' THEN 1 WHEN 'martes' THEN 2 WHEN 'miercoles' THEN 3 WHEN 'jueves' THEN 4 WHEN 'viernes' THEN 5 WHEN 'sabado' THEN 6 ELSE 7 END")]);
        $restriccionProducto = fn ($q) => $q->where('activo', true)->when($filtros['producto'] ?? null, fn ($p, $valor) => $p->where('nombre', 'like', "%{$valor}%"))->orderByRaw('orden is null')->orderBy('orden')->orderBy('nombre');
        $categoriasProducto = $negocio->categoriasProducto()->where('activo', true)
            ->when($filtros['categoria_producto'] ?? null, fn ($q, $id) => $q->whereKey($id))
            ->whereHas('productos', $restriccionProducto)->with(['productos' => $restriccionProducto])
            ->orderByRaw('orden is null')->orderBy('orden')->orderBy('nombre')->get();
        $productosSinCategoria = $negocio->productos()->whereNull('categoria_producto_id')->where('activo', true)
            ->when($filtros['producto'] ?? null, fn ($q, $valor) => $q->where('nombre', 'like', "%{$valor}%"))
            ->when($filtros['categoria_producto'] ?? null, fn ($q) => $q->whereRaw('1 = 0'))->orderBy('nombre')->get();

        return view('cliente.negocio', compact('negocio', 'categoriasProducto', 'productosSinCategoria', 'filtros'));
    }

    public function proximamente(): View
    {
        return view('cliente.proximamente');
    }

    public function perfil(): View
    {
        return view('cliente.perfil');
    }
}
