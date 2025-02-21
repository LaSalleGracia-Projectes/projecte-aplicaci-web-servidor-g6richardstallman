<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrada extends Model
{
    use HasFactory;
    
    protected $table = 'entrada';
    protected $primaryKey = 'idEntrada';
    public $timestamps = false;

    protected $fillable = [
        'fechaVenta',
        'nombrePersona',
        'apellido1',
        'apellido2',
        'idEvento'
    ];

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento');
    }
}