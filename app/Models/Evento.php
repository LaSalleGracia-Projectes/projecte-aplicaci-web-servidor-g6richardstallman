<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evento extends Model
{
    protected $table = 'evento';
    protected $primaryKey = 'idEvento';
    public $timestamps = false;

    protected $fillable = [
        'idEmpresa',
        'nombreEvento',
        'fechaEvento',
        'lugar'
    ];

    // Relación con Empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'idEmpresa');
    }

    // Relación con Entrada
    public function entradas(): HasMany
    {
        return $this->hasMany(Entrada::class, 'idEvento');
    }
}