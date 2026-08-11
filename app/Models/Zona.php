<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'activo', 'orden'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'orden' => 'integer'];
    }

    public function negocios(): HasMany
    {
        return $this->hasMany(Negocio::class);
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(DireccionUsuario::class);
    }
}
