<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'monto_total',
        'descuento',
        'impuestos',
        'subtotal',
        'venta_entrada_id'
    ];

    // Relación con VentaEntrada
    public function ventaEntrada(): BelongsTo
    {
        return $this->belongsTo(VentaEntrada::class, 'venta_entrada_id');
    }
}