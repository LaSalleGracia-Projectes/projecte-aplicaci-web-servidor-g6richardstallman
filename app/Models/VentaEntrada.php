<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaEntrada extends Model
{
    protected $table = 'ventaentrada';
    protected $primaryKey = 'idVentaEntrada';
    public $timestamps = false;

    protected $fillable = [
        'dniCliente',
        'fechaCompra',
        'estadoPago',
        'subtotal',
        'impuestos',
        'descuento',
        'montoTotal',
        'idEntrada',
        'idPago'
    ];

    // Relación con Clients
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'dniCliente', 'dni');
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