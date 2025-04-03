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
        'impuestos',
        'idEntrada',
        'idPago',
        'idParticipante',
        'precio',
        'fecha_compra'
    ];

    // Constante para el IVA (21%)
    const IVA = 0.21;

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
    
    // Método para calcular y establecer los valores automáticamente
    public function setPrecioAndCalculateValues($precio)
    {
        $this->precio = $precio;
        // Calculamos directamente los impuestos (21% del precio)
        $this->impuestos = round($precio * self::IVA, 2);
        
        return $this;
    }
}