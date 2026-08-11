<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $negocios = $request->user()->negocios()->with(['categoria', 'subcategorias'])->get();
        $seleccionado = $negocios->firstWhere('id', $request->session()->get('negocio_actual_id')) ?? $negocios->first();
        if ($seleccionado) {
            $request->session()->put('negocio_actual_id', $seleccionado->id);
        } if ($seleccionado) {
            $seleccionado->loadCount(['productos', 'productos as productos_activos_count' => fn ($q) => $q->where('activo', true), 'productos as productos_inactivos_count' => fn ($q) => $q->where('activo', false)]);
        }

return view('negocio.inicio', ['negocios' => $negocios, 'negocio' => $seleccionado]);
    }

    public function seleccionar(Request $request, Negocio $negocio): RedirectResponse
    {
        abort_unless($negocio->usuario_id === $request->user()->id, 403);
        $request->session()->put('negocio_actual_id', $negocio->id);

        return redirect()->route('negocio.inicio')->with('estado', 'Negocio seleccionado.');
    }
}
