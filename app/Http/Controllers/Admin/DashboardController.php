<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use App\Models\Zona;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.inicio', [
            'sinZonasActivas' => ! Zona::where('activo', true)->exists(),
            'metricas' => [
                'negocios' => Negocio::count(),
                'pendientes' => Negocio::where('estado', 'pendiente')->count(),
                'aprobados' => Negocio::where('estado', 'aprobado')->count(),
                'categoriasActivas' => CategoriaNegocio::where('activo', true)->count(),
                'usuarios' => Usuario::count(),
            ],
        ]);
    }
}
