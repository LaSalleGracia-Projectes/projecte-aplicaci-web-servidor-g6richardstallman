<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'idPago';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email',
        'tipoPlan'
    ];

    // Relación con Plan
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'tipoPlan');
    }
}