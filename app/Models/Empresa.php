<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Empresa extends Model
{
    protected $table = 'empresa';
    protected $primaryKey = 'idEmpresa';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email',
        'tipoPlan'
    ];

    // Relación con Evento
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'idEmpresa');
    }

    // Relación con Estadistiques
    public function estadistiques(): HasMany
    {
        return $this->hasMany(Estadistiques::class, 'idEmpresa');
    }
}