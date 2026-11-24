<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesional extends Model
{
    protected $table = 'profesionales';

    protected $fillable = [
        'user_id', 'establecimiento_id', 'matricula', 'especialidad',
        'biografia', 'anios_experiencia', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('profesionales.activo', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'profesional_servicio');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class)->orderBy('dia_semana')->orderBy('hora_inicio');
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class)->orderBy('inicio');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class);
    }

    public function getNombreAttribute(): string
    {
        return $this->user?->nombre_completo ?? '';
    }
}
