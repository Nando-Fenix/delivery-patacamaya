<?php

namespace App\Models;

use App\Enums\EstadoPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $fillable = ['usuario_id', 'negocio_id', 'direccion_usuario_id', 'estado', 'subtotal', 'costo_delivery', 'total', 'metodo_pago', 'observaciones', 'motivo_rechazo', 'direccion_nombre', 'direccion_referencia', 'zona_nombre', 'latitud', 'longitud', 'fecha_pedido'];

    protected function casts(): array
    {
        return ['estado' => EstadoPedido::class, 'subtotal' => 'decimal:2', 'costo_delivery' => 'decimal:2', 'total' => 'decimal:2', 'latitud' => 'decimal:7', 'longitud' => 'decimal:7', 'fecha_pedido' => 'datetime'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(DireccionUsuario::class, 'direccion_usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }
}
