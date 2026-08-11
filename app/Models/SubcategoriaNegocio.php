<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubcategoriaNegocio extends Model
{
    protected $table = 'subcategorias_negocio';

    protected $fillable = ['categoria_negocio_id', 'nombre', 'descripcion', 'icono', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaNegocio::class, 'categoria_negocio_id');
    }

    public function negocios(): BelongsToMany
    {
        return $this->belongsToMany(Negocio::class, 'negocio_subcategoria', 'subcategoria_negocio_id', 'negocio_id');
    }
}
