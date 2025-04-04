<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Entrada extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'entrada';
    protected $primaryKey = 'idEntrada';
    public $timestamps = true;

    protected $fillable = [
        'fecha_venta',
        'nombre_persona',
        'precio',
        'codigo',
        'estado',
        'idEvento',
        'idTipoEntrada'
    ];

    // Relación con Tipo de Entrada
    public function tipoEntrada()
    {
        return $this->belongsTo(TipoEntrada::class, 'idTipoEntrada', 'idTipoEntrada');
    }

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento', 'idEvento');
    }

    // Relación con VentaEntrada
    public function ventaEntradas(): HasMany
    {
        return $this->hasMany(VentaEntrada::class, 'idEntrada', 'idEntrada');
    }
}