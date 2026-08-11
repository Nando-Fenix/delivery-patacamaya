<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\GuardarDireccionRequest;
use App\Models\DireccionUsuario;
use App\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DireccionController extends Controller
{
    public function index(): View
    {
        $direcciones = auth()->user()->direcciones()->with('zona')->orderByDesc('predeterminada')->latest()->get();

        return view('cliente.direcciones.index', compact('direcciones'));
    }

    public function create(): View
    {
        return view('cliente.direcciones.form', ['direccion' => new DireccionUsuario, 'zonas' => Zona::where('activo', true)->orderBy('nombre')->get()]);
    }

    public function store(GuardarDireccionRequest $r): RedirectResponse
    {
        $this->guardar($r->validated());

        return redirect()->route('cliente.direcciones.index')->with('estado', 'Dirección guardada.');
    }

    public function edit(DireccionUsuario $direccion): View
    {
        $this->propia($direccion);

        return view('cliente.direcciones.form', ['direccion' => $direccion, 'zonas' => Zona::where('activo', true)->orderBy('nombre')->get()]);
    }

    public function update(GuardarDireccionRequest $r, DireccionUsuario $direccion): RedirectResponse
    {
        $this->propia($direccion);
        $this->guardar($r->validated(), $direccion);

        return redirect()->route('cliente.direcciones.index')->with('estado', 'Dirección actualizada.');
    }

    public function estado(DireccionUsuario $direccion): RedirectResponse
    {
        $this->propia($direccion);
        $direccion->update(['activo' => ! $direccion->activo, 'predeterminada' => false]);

        return back()->with('estado', 'Estado actualizado.');
    }

    public function predeterminada(DireccionUsuario $direccion): RedirectResponse
    {
        $this->propia($direccion);
        abort_unless($direccion->activo, 422);
        DB::transaction(function () use ($direccion) {
            auth()->user()->direcciones()->update(['predeterminada' => false]);
            $direccion->update(['predeterminada' => true]);
        });

        return back()->with('estado', 'Dirección predeterminada actualizada.');
    }

    private function guardar(array $datos, ?DireccionUsuario $direccion = null): void
    {
        DB::transaction(function () use ($datos, $direccion) {
            if ($datos['predeterminada']) {
                auth()->user()->direcciones()->update(['predeterminada' => false]);
            }if ($direccion) {
                $direccion->update($datos);
            } else {
                auth()->user()->direcciones()->create($datos);
            }
        });
    }

    private function propia(DireccionUsuario $d): void
    {
        abort_unless($d->usuario_id === auth()->id(), 403);
    }
}
