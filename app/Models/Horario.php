<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = ['profesional_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    public const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public function getDiaNombreAttribute(): string
    {
        return self::DIAS[$this->dia_semana] ?? '';
    }

    public function getRangoAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5).' – '.substr($this->hora_fin, 0, 5);
    }
}
