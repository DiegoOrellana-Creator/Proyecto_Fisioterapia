<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    protected $table = 'citas';

    public const SOLICITADA = 'solicitada';
    public const CONFIRMADA = 'confirmada';
    public const REPROGRAMADA = 'reprogramada';
    public const CANCELADA = 'cancelada';
    public const ATENDIDA = 'atendida';
    public const NO_ASISTIO = 'no_asistio';

    /** Estados que ocupan un espacio en la agenda. */
    public const ACTIVOS = [self::SOLICITADA, self::CONFIRMADA, self::REPROGRAMADA];

    public const ETIQUETAS = [
        self::SOLICITADA => 'Solicitada',
        self::CONFIRMADA => 'Confirmada',
        self::REPROGRAMADA => 'Reprogramada',
        self::CANCELADA => 'Cancelada',
        self::ATENDIDA => 'Atendida',
        self::NO_ASISTIO => 'No asistió',
    ];

    protected $fillable = [
        'codigo', 'paciente_id', 'profesional_id', 'servicio_id', 'establecimiento_id',
        'fecha', 'hora_inicio', 'hora_fin', 'estado', 'precio',
        'numero_sesion', 'total_sesiones', 'grupo',
        'nota_paciente', 'nota_profesional', 'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'precio' => 'decimal:2',
    ];

    public function scopeActivas(Builder $q): Builder
    {
        return $q->whereIn('citas.estado', self::ACTIVOS);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialCita::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function valoracion(): HasOne
    {
        return $this->hasOne(Valoracion::class);
    }

    public function getInicioAttribute(): Carbon
    {
        return Carbon::parse($this->fecha->format('Y-m-d').' '.$this->hora_inicio);
    }

    public function getEtiquetaEstadoAttribute(): string
    {
        return self::ETIQUETAS[$this->estado] ?? $this->estado;
    }

    public function getRangoHoraAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5).' – '.substr($this->hora_fin, 0, 5);
    }

    public function esActiva(): bool
    {
        return in_array($this->estado, self::ACTIVOS, true);
    }

    public function yaPaso(): bool
    {
        return $this->inicio->isPast();
    }

    /** El paciente puede cancelar o reprogramar mientras la cita esté activa y no haya empezado. */
    public function modificablePorPaciente(): bool
    {
        return $this->esActiva() && ! $this->yaPaso();
    }

    /** Se puede registrar asistencia cuando la cita ya empezó y sigue activa. */
    public function admiteAsistencia(): bool
    {
        return $this->esActiva() && $this->yaPaso();
    }

    public function puedeValorarse(): bool
    {
        return $this->estado === self::ATENDIDA && ! $this->valoracion;
    }

    public function cambiarEstado(string $nuevo, ?User $por = null, ?string $detalle = null): void
    {
        $anterior = $this->estado;
        $this->estado = $nuevo;
        $this->save();

        $this->historial()->create([
            'user_id' => $por?->id,
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'detalle' => $detalle,
        ]);
    }
}
