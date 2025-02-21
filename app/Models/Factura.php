<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    protected $table = 'factura';
    protected $primaryKey = 'idFactura';
    public $timestamps = false;

    protected $fillable = [
        'idCliente',
        'montoTotal',
        'descuento',
        'impostos',
        'subtotal',
        'idEntrada',
        'idPago'
    ];

    // Relación con Clients
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'idCliente');
    }

    // Relación con Entrada
    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'idEntrada');
    }

    // Relación con Pago
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'idPago');
    }
}