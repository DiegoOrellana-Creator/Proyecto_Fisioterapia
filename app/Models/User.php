<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_PACIENTE = 'paciente';
    public const ROL_PROFESIONAL = 'profesional';
    public const ROL_ADMIN = 'admin';

    protected $fillable = [
        'nombre', 'apellido', 'ci', 'telefono', 'email', 'password',
        'rol', 'establecimiento_id', 'activo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }

    public function getInicialesAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->nombre, 0, 1).mb_substr($this->apellido, 0, 1));
    }

    public function esPaciente(): bool
    {
        return $this->rol === self::ROL_PACIENTE;
    }

    public function esProfesional(): bool
    {
        return $this->rol === self::ROL_PROFESIONAL;
    }

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    /** Administrador general: no está atado a un establecimiento. */
    public function esAdminGeneral(): bool
    {
        return $this->esAdmin() && $this->establecimiento_id === null;
    }

    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    /** Ruta de inicio según el rol. */
    public function rutaInicio(): string
    {
        return match ($this->rol) {
            self::ROL_ADMIN => route('admin.dashboard'),
            self::ROL_PROFESIONAL => route('profesional.agenda'),
            default => route('paciente.citas'),
        };
    }
}
