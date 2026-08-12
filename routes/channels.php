<?php

use App\Models\Negocio;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('negocio.{negocioId}', function ($user, int $negocioId) {
    return Negocio::query()
        ->whereKey($negocioId)
        ->where('usuario_id', $user->id)
        ->exists();
});

Broadcast::channel('cliente.{usuarioId}', function ($user, int $usuarioId) {
    return (int) $user->id === $usuarioId && $user->rol->nombre === 'cliente';
});
