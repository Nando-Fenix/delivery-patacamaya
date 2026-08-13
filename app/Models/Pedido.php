<?php

namespace App\Models;

use App\Enums\EstadoPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $fillable = ['usuario_id', 'negocio_id', 'repartidor_id', 'direccion_usuario_id', 'estado', 'subtotal', 'costo_delivery', 'total', 'metodo_pago', 'observaciones', 'motivo_rechazo', 'direccion_nombre', 'direccion_referencia', 'zona_nombre', 'latitud', 'longitud', 'repartidor_latitud', 'repartidor_longitud', 'repartidor_precision', 'ubicacion_repartidor_actualizada_en', 'fecha_pedido'];

    protected function casts(): array
    {
        return ['estado' => EstadoPedido::class, 'subtotal' => 'decimal:2', 'costo_delivery' => 'decimal:2', 'total' => 'decimal:2', 'latitud' => 'decimal:7', 'longitud' => 'decimal:7', 'repartidor_latitud' => 'decimal:7', 'repartidor_longitud' => 'decimal:7', 'repartidor_precision' => 'decimal:2', 'ubicacion_repartidor_actualizada_en' => 'datetime', 'fecha_pedido' => 'datetime'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'repartidor_id');
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
