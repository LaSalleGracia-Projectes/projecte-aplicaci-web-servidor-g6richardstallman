<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clients extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'idCliente';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido1',
        'apellido2',
        'email',
        'telefono',
        'dni'
    ];

    // Relación con VentaEntrada
    public function ventaEntradas(): HasMany
    {
        return $this->hasMany(VentaEntrada::class, 'dniCliente', 'dni');
    }

    // Relación con Valoracion
    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class, 'idCliente');
    }

    // Relación con Factura
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'idCliente');
    }
}