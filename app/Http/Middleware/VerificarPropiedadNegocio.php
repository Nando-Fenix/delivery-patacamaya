<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPropiedadNegocio
{
    public function handle(Request $request, Closure $next): Response
    {
        $negocio = $request->route('negocio');
        abort_unless($negocio && $request->user()->id === $negocio->usuario_id, 403);

        return $next($request);
    }
}
