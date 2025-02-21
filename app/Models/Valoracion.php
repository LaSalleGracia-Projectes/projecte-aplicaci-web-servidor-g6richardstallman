<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Valoracion extends Model
{
    protected $table = 'valoracion';
    protected $primaryKey = 'idValoracion';
    public $timestamps = false;

    protected $fillable = [
        'idCliente',
        'idEvento',
        'puntuacion',
        'comentario'
    ];

    // Relación con Clients
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'idCliente');
    }

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento');
    }
}