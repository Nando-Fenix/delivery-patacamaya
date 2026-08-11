<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    protected $table = 'detalles_pedido';

    protected $fillable = ['pedido_id', 'producto_id', 'nombre_producto', 'precio_unitario', 'cantidad', 'subtotal'];

    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2', 'subtotal' => 'decimal:2', 'cantidad' => 'integer'];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
