<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establecimiento extends Model
{
    protected $table = 'establecimientos';

    protected $fillable = [
        'nombre', 'slug', 'descripcion', 'direccion', 'zona',
        'telefono', 'whatsapp', 'email', 'horario_texto', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public const ZONAS = [
        'Centro', 'Equipetrol', 'Norte', 'Urbarí', 'Las Palmas',
        'Plan 3000', 'Villa 1ro de Mayo', 'Pampa de la Isla', 'Sur',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    public function profesionales(): HasMany
    {
        return $this->hasMany(Profesional::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class);
    }

    public function administradores(): HasMany
    {
        return $this->hasMany(User::class)->where('rol', User::ROL_ADMIN);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $numero = preg_replace('/\D/', '', $this->whatsapp);
        if (strlen($numero) === 8) {
            $numero = '591'.$numero;
        }

        return 'https://wa.me/'.$numero;
    }
}
