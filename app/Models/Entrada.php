<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrada extends Model
{
    use HasFactory;

    protected $table = 'entradas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'fecha_venta',
        'nombre_persona',
        'evento_id'
    ];

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    // Relación con VentaEntrada
    public function ventaEntradas(): HasMany
    {
        return $this->hasMany(VentaEntrada::class, 'entrada_id');
    }
}