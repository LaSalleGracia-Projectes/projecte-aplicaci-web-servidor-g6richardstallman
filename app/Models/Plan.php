<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    protected $table = 'plan';
    protected $primaryKey = 'idPlan';
    public $timestamps = false;

    protected $fillable = [
        'nombrePlan',
        'funcionalitats'
    ];

    // Relación con Pago
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'tipoPlan');
    }

    // Relación con Estadistiques
    public function estadistiques(): HasMany
    {
        return $this->hasMany(Estadistiques::class, 'tipusPlan');
    }
}