<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActualizarClasificacionNegocioRequest;
use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NegocioController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'categoria' => ['nullable', 'integer', 'exists:categorias_negocio,id'],
            'estado' => ['nullable', Rule::in(['pendiente', 'aprobado', 'rechazado'])],
            'activo' => ['nullable', Rule::in(['1', '0'])],
        ]);

        $negocios = Negocio::query()
            ->with(['usuario', 'categoria', 'subcategorias'])
            ->when($filtros['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($subconsulta) use ($buscar) {
                    $subconsulta->where('nombre', 'like', "%{$buscar}%")
                        ->orWhereHas('usuario', fn ($usuarios) => $usuarios
                            ->where('nombres', 'like', "%{$buscar}%")
                            ->orWhere('apellidos', 'like', "%{$buscar}%"));
                });
            })
            ->when($filtros['categoria'] ?? null, fn ($query, $categoria) => $query->where('categoria_negocio_id', $categoria))
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when(array_key_exists('activo', $filtros), fn ($query) => $query->where('activo', (bool) $filtros['activo']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.negocios.index', [
            'negocios' => $negocios,
            'categorias' => CategoriaNegocio::orderBy('nombre')->get(),
            'filtros' => $filtros,
        ]);
    }

    public function show(Negocio $negocio): View
    {
        $negocio->load(['usuario', 'categoria', 'subcategorias']);

        return view('admin.negocios.show', compact('negocio'));
    }

    public function editarClasificacion(Negocio $negocio): View
    {
        $negocio->load('subcategorias');

        return view('admin.negocios.clasificacion', [
            'negocio' => $negocio,
            'categorias' => CategoriaNegocio::with(['subcategorias' => fn ($q) => $q->orderBy('nombre')])->orderBy('nombre')->get(),
        ]);
    }

    public function actualizarClasificacion(ActualizarClasificacionNegocioRequest $request, Negocio $negocio): RedirectResponse
    {
        $datos = $request->validated();
        DB::transaction(function () use ($negocio, $datos) {
            $negocio->update(['categoria_negocio_id' => $datos['categoria_negocio_id']]);
            $negocio->subcategorias()->sync($datos['subcategorias'] ?? []);
        });

        return redirect()->route('administrador.negocios.show', $negocio)->with('estado', 'Clasificación del negocio actualizada correctamente.');
    }

    public function cambiarEstado(Request $request, Negocio $negocio): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'aprobado', 'rechazado'])],
        ]);
        $negocio->update($datos);

        return back()->with('estado', 'Negocio '.match ($datos['estado']) {
            'aprobado' => 'aprobado',
            'rechazado' => 'rechazado',
            default => 'marcado como pendiente',
        }.' correctamente.');
    }

    public function cambiarActivo(Negocio $negocio): RedirectResponse
    {
        $negocio->update(['activo' => ! $negocio->activo]);
        $accion = $negocio->activo ? 'activado' : 'desactivado';

        return back()->with('estado', "Negocio {$accion} correctamente.");
    }
}
