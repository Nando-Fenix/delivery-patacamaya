<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioNegocio extends Model
{
    protected $table = 'horarios_negocio';

    protected $fillable = ['negocio_id', 'dia_semana', 'hora_apertura', 'hora_cierre', 'cerrado'];

    protected function casts(): array
    {
        return ['cerrado' => 'boolean'];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }
}
