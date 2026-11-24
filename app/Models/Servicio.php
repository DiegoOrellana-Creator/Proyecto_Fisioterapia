<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'establecimiento_id', 'nombre', 'categoria', 'descripcion',
        'duracion_min', 'precio', 'sesiones_sugeridas', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
    ];

    public const CATEGORIAS = [
        'Evaluación',
        'Traumatología',
        'Deportiva',
        'Neurológica',
        'Respiratoria',
        'Suelo pélvico',
        'Pediátrica',
        'Geriátrica',
        'Terapia manual',
    ];

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('servicios.activo', true);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'profesional_servicio');
    }

    public function getPrecioFormateadoAttribute(): string
    {
        return 'Bs '.number_format((float) $this->precio, 2, ',', '.');
    }
}
