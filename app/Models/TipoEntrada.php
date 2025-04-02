<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TipoEntrada extends Model
{
    use HasFactory;

    protected $table = 'tipo_entrada';
    protected $primaryKey = 'idTipoEntrada';

    protected $fillable = [
        'idEvento',
        'nombre',
        'precio',
        'cantidad_disponible',
        'entradas_vendidas',
        'descripcion',
        'es_ilimitado',
        'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'cantidad_disponible' => 'integer',
        'entradas_vendidas' => 'integer',
        'es_ilimitado' => 'boolean',
        'activo' => 'boolean'
    ];

    protected $appends = ['disponibilidad'];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idEvento', 'idEvento');
    }

    // Verificar si hay entradas disponibles
    public function hayDisponibilidad(int $cantidad = 1): bool
    {
        if ($this->es_ilimitado) {
            return true;
        }

        return $this->cantidad_disponible >= ($this->entradas_vendidas + $cantidad);
    }

    // Obtener número de entradas disponibles
    public function getDisponibilidadAttribute()
    {
        if ($this->es_ilimitado) {
            return 'Ilimitado';
        }

        return $this->cantidad_disponible - $this->entradas_vendidas;
    }

    // Procesar venta de entradas
    public function venderEntradas(int $cantidad = 1): bool
    {
        if (!$this->hayDisponibilidad($cantidad)) {
            return false;
        }

        $this->entradas_vendidas += $cantidad;
        return $this->save();
    }

    // Scope para tipos de entrada activos
    public function scopeActivos(Builder $query)
    {
        return $query->where('activo', true);
    }

    // Scope para tipos de entrada con disponibilidad
    public function scopeConDisponibilidad(Builder $query)
    {
        return $query->where(function($q) {
            $q->where('es_ilimitado', true)
              ->orWhereRaw('cantidad_disponible > entradas_vendidas');
        })->where('activo', true);
    }
} 