<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sugerencia extends Model
{
    protected $table = 'sugerencias';

    protected $fillable = ['user_id', 'establecimiento', 'zona', 'contacto', 'comentario', 'revisada'];

    protected $casts = ['revisada' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
