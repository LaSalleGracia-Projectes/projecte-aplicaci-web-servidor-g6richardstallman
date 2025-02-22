<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaEntrada extends Model
{
    use HasFactory;

    protected $table = 'venta_entrada';
    protected $primaryKey = 'idVentaEntrada';
    public $timestamps = true;

    protected $fillable = [
        'estado_pago',
        'subtotal',
        'impuestos',
        'descuento',
        'monto_total',
        'idEntrada',
        'idPago',
        'idParticipante'
    ];

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'idEntrada');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'idPago');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'idParticipante');
    }
}