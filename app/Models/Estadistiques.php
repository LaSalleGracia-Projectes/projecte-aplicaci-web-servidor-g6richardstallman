<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estadistiques extends Model
{
    use HasFactory;
    
    protected $table = 'estadistiques';
    protected $primaryKey = 'idEstadistiques';
    public $timestamps = false;

    protected $fillable = [
        'idEmpresa',
        'idEvento',
        'visitesTotals',
        'entradesVendes',
        'tipusPlan',
        'valoracioProm'
    ];

    // Relación con Empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idEmpresa');
    }

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento');
    }

    // Relación con Plan
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'tipusPlan');
    }
}