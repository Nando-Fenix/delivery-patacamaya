<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionUsuario extends Model
{
    protected $table = 'direcciones_usuario';

    protected $fillable = ['usuario_id', 'zona_id', 'nombre', 'direccion_referencia', 'latitud', 'longitud', 'predeterminada', 'activo'];

    protected function casts(): array
    {
        return ['predeterminada' => 'boolean', 'activo' => 'boolean', 'latitud' => 'decimal:7', 'longitud' => 'decimal:7'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }
}
