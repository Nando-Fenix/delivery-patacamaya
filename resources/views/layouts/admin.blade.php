@extends('layouts.app')

@section('contenido')
<div class="admin-layout">
    <aside class="admin-sidebar card border-0 shadow-sm d-none d-lg-block">
        <div class="card-body p-3">
            <div class="text-uppercase small fw-bold text-secondary mb-2">Administración</div>
            <nav class="nav nav-pills flex-column gap-1" aria-label="Menú administrativo">
                <a class="nav-link {{ request()->routeIs('administrador.inicio') ? 'active' : '' }}" href="{{ route('administrador.inicio') }}">
                    <i class="bi bi-grid me-2"></i>Inicio
                </a>
                <a class="nav-link {{ request()->routeIs('administrador.subcategorias.*') ? 'active' : '' }}" href="{{ route('administrador.subcategorias.index') }}"><i class="bi bi-diagram-2 me-2"></i>Subcategorías</a>
                <a class="nav-link {{ request()->routeIs('administrador.categorias.*') ? 'active' : '' }}" href="{{ route('administrador.categorias.index') }}">
                    <i class="bi bi-tags me-2"></i>Categorías
                </a>
                <a class="nav-link {{ request()->routeIs('administrador.negocios.*') ? 'active' : '' }}" href="{{ route('administrador.negocios.index') }}">
                    <i class="bi bi-shop me-2"></i>Negocios
                </a>
                <a class="nav-link {{ request()->routeIs('administrador.zonas.*') ? 'active' : '' }}" href="{{ route('administrador.zonas.index') }}"><i class="bi bi-geo-alt me-2"></i>Zonas</a>
            </nav>
            <hr>
            <div class="small text-secondary px-2 mb-2"><i class="bi bi-person-circle me-2"></i>{{ auth()->user()->nombres }}</div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-danger w-100" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button></form>
        </div>
    </aside>
    <div class="admin-content">
        @yield('contenido-admin')
    </div>
</div>

<nav class="mobile-bottom-nav d-lg-none" aria-label="Navegación móvil">
    <a class="mobile-nav-item {{ request()->routeIs('administrador.inicio') ? 'active' : '' }}" href="{{ route('administrador.inicio') }}"><i class="bi bi-house"></i><span>Inicio</span></a>
    <a class="mobile-nav-item {{ request()->routeIs('administrador.categorias.*') ? 'active' : '' }}" href="{{ route('administrador.categorias.index') }}"><i class="bi bi-tags"></i><span>Categorías</span></a>
    <a class="mobile-nav-item {{ request()->routeIs('administrador.negocios.*') ? 'active' : '' }}" href="{{ route('administrador.negocios.index') }}"><i class="bi bi-shop"></i><span>Negocios</span></a>
    <a class="mobile-nav-item {{ request()->routeIs('administrador.zonas.*') ? 'active' : '' }}" href="{{ route('administrador.zonas.index') }}"><i class="bi bi-geo-alt"></i><span>Zonas</span></a>
    <button class="mobile-nav-item {{ request()->routeIs('administrador.subcategorias.*') ? 'active' : '' }}" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMas"><i class="bi bi-grid-3x3-gap"></i><span>Más</span></button>
</nav>
<div class="offcanvas offcanvas-bottom mobile-more-menu" tabindex="-1" id="menuMas" aria-labelledby="menuMasTitulo"><div class="offcanvas-header"><h2 class="offcanvas-title h5" id="menuMasTitulo">Más opciones</h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button></div><div class="offcanvas-body"><a class="more-menu-link" href="{{ route('administrador.subcategorias.index') }}"><i class="bi bi-diagram-2"></i><span><strong>Subcategorías</strong><small>Clasificaciones de negocios</small></span></a><div class="more-menu-link"><i class="bi bi-person-circle"></i><span><strong>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</strong><small>Perfil administrador</small></span></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="more-menu-link text-danger w-100 border-0 bg-transparent" type="submit"><i class="bi bi-box-arrow-right"></i><span><strong>Cerrar sesión</strong></span></button></form></div></div>

<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="tituloConfirmacion" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="tituloConfirmacion">Confirmar acción</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="mensajeConfirmacion">¿Deseas continuar?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarAccion">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const elementoModal = document.getElementById('modalConfirmacion');
    const botonConfirmar = document.getElementById('confirmarAccion');
    const mensaje = document.getElementById('mensajeConfirmacion');
    let formularioPendiente = null;

    document.querySelectorAll('.requiere-confirmacion').forEach((formulario) => {
        formulario.addEventListener('submit', (evento) => {
            evento.preventDefault();
            formularioPendiente = formulario;
            mensaje.textContent = formulario.dataset.mensaje || '¿Deseas continuar?';
            bootstrap.Modal.getOrCreateInstance(elementoModal).show();
        });
    });

    botonConfirmar.addEventListener('click', () => {
        if (formularioPendiente) formularioPendiente.submit();
    });
});
</script>
@endsection
