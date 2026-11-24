<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialCita extends Model
{
    protected $table = 'historial_citas';

    public const UPDATED_AT = null;

    protected $fillable = ['cita_id', 'user_id', 'estado_anterior', 'estado_nuevo', 'detalle'];

    protected $casts = ['created_at' => 'datetime'];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
