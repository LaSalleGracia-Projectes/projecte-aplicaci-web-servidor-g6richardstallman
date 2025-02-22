<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Valoracion extends Model
{
    use HasFactory;

    protected $table = 'valoracion';
    protected $primaryKey = 'idValoracion';
    public $timestamps = true;

    protected $fillable = [
        'puntuacion',
        'comentario',
        'idEvento',
        'idParticipante'
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'idParticipante');
    }
}