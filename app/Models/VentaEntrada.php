<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaEntrada extends Model
{
    use HasFactory;

    protected $table = 'venta_entradas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'estado_pago',
        'subtotal',
        'impuestos',
        'descuento',
        'monto_total',
        'entrada_id',
        'pago_id',
        'participante_id'
    ];

    // Relación con Entrada
    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'entrada_id');
    }

    // Relación con Pago
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}