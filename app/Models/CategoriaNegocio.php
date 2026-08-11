<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaNegocio extends Model
{
    protected $table = 'categorias_negocio';

    protected $fillable = ['nombre', 'descripcion', 'icono', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function negocios(): HasMany
    {
        return $this->hasMany(Negocio::class);
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(SubcategoriaNegocio::class);
    }
}
