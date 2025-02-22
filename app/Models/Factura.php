<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'factura';
    protected $primaryKey = 'idFactura';
    public $timestamps = true;

    protected $fillable = [
        'montoTotal',
        'descuento',
        'impostos',
        'subtotal',
        'idParticipante',
        'idEntrada',
        'idPago'
    ];

    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'idParticipante');
    }

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'idEntrada');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'idPago');
    }
}