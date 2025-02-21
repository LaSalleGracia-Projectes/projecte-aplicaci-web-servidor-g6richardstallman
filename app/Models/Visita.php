<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visita extends Model
{
    protected $table = 'visita';
    protected $primaryKey = 'idVisita';
    public $timestamps = false;

    protected $fillable = [
        'idClient',
        'idEmpresa',
        'idEvent',
        'fechaVisita',
        'tipoVisita'
    ];

    // Relación con Clients
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'idClient');
    }

    // Relación con Empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idEmpresa');
    }

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvent');
    }
}