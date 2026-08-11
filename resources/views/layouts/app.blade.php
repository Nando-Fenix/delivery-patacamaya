<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b4f32">
    <meta name="description" content="Delivery local para Patacamaya">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon-192.svg" type="image/svg+xml">
    <title>@yield('titulo', 'Delivery Patacamaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('estilos')
</head>
<body>
    <header class="app-header text-white shadow-sm">
        <nav class="navbar navbar-dark app-shell px-3 py-3">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="/">
                <span class="brand-mark"><i class="bi bi-bicycle" aria-hidden="true"></i></span>
                <span>Delivery Patacamaya</span>
            </a>
            @auth
                @unless(request()->routeIs('administrador.*') || request()->routeIs('negocio.*') || request()->routeIs('cliente.*'))
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Salir
                    </button>
                </form>
                @endunless
            @endauth
        </nav>
    </header>

    <main class="app-main app-shell py-4 py-md-5">
        @if (session('estado'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('estado') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @yield('contenido')
    </main>
    @stack('scripts')
</body>
</html>
