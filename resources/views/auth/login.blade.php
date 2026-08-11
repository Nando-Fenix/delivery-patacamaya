@extends('layouts.app')

@section('titulo', 'Iniciar sesión — Delivery Patacamaya')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <section class="card login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle display-4 text-success" aria-hidden="true"></i>
                    <h1 class="h3 mt-2 mb-1">Bienvenido</h1>
                    <p class="text-secondary mb-0">Ingresa a tu cuenta para continuar.</p>
                </div>

                <form method="POST" action="{{ route('login.autenticar') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="correo">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input class="form-control @error('correo') is-invalid @enderror" id="correo" name="correo" type="email" value="{{ old('correo') }}" autocomplete="email" required autofocus>
                            @error('correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="recordarme" name="recordarme" type="checkbox" value="1">
                        <label class="form-check-label" for="recordarme">Mantener sesión iniciada</label>
                    </div>
                    <button class="btn btn-primary btn-lg w-100" type="submit">
                        Ingresar <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
