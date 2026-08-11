<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AutenticacionController extends Controller
{
    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credenciales, 'activo' => true], $request->boolean('recordarme'))) {
            throw ValidationException::withMessages([
                'correo' => 'Las credenciales no son válidas o el usuario está inactivo.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->rutaInicio(Auth::user()->rol->nombre));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('estado', 'Sesión cerrada correctamente.');
    }

    private function rutaInicio(string $rol): string
    {
        return route("{$rol}.inicio");
    }
}
