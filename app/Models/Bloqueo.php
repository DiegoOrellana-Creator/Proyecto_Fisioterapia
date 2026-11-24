<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bloqueo extends Model
{
    protected $table = 'bloqueos';

    protected $fillable = ['profesional_id', 'inicio', 'fin', 'motivo'];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }
}
