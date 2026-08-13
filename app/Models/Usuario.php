<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = ['rol_id', 'nombres', 'apellidos', 'telefono', 'correo', 'password', 'activo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'password' => 'hashed'];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(DireccionUsuario::class);
    }

    public function negocios(): HasMany
    {
        return $this->hasMany(Negocio::class);
    }

    public function carrito(): HasOne
    {
        return $this->hasOne(Carrito::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Pedido::class, 'repartidor_id');
    }
}
