<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorito extends Model
{
    use HasFactory;

    protected $table = 'favorito';
    protected $primaryKey = 'idFavorito';
    public $timestamps = true;

    protected $fillable = [
        'idParticipante',
        'idEvento',
        'fechaAgregado'
    ];

    /**
     * Obtener el participante al que pertenece este favorito
     */
    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'idParticipante', 'idParticipante');
    }

    /**
     * Obtener el evento marcado como favorito
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento', 'idEvento');
    }
} 