<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Negocio extends Model
{
    protected $fillable = ['usuario_id', 'categoria_negocio_id', 'zona_id', 'nombre', 'descripcion', 'telefono', 'direccion_referencia', 'latitud', 'longitud', 'estado', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'latitud' => 'decimal:7', 'longitud' => 'decimal:7'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaNegocio::class, 'categoria_negocio_id');
    }

    public function subcategorias(): BelongsToMany
    {
        return $this->belongsToMany(SubcategoriaNegocio::class, 'negocio_subcategoria', 'negocio_id', 'subcategoria_negocio_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioNegocio::class);
    }

    public function categoriasProducto(): HasMany
    {
        return $this->hasMany(CategoriaProducto::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function scopeVisiblesParaCliente(Builder $query): Builder
    {
        return $query->where('estado', 'aprobado')->where('activo', true)
            ->whereHas('categoria', fn (Builder $categoria) => $categoria->where('activo', true));
    }

    public function estaAbierto(?Carbon $momento = null): bool
    {
        $momento ??= now();
        $dias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
        $horario = $this->relationLoaded('horarios') ? $this->horarios->firstWhere('dia_semana', $dias[$momento->dayOfWeekIso]) : $this->horarios()->where('dia_semana', $dias[$momento->dayOfWeekIso])->first();

        return $horario && ! $horario->cerrado && $horario->hora_apertura && $horario->hora_cierre
            && $momento->format('H:i:s') >= $horario->hora_apertura && $momento->format('H:i:s') < $horario->hora_cierre;
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
