<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    protected $fillable = ['negocio_id', 'categoria_producto_id', 'nombre', 'descripcion', 'precio', 'imagen', 'activo', 'disponible', 'orden'];

    protected function casts(): array
    {
        return ['precio' => 'decimal:2', 'activo' => 'boolean', 'disponible' => 'boolean', 'orden' => 'integer'];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_producto_id');
    }
}
