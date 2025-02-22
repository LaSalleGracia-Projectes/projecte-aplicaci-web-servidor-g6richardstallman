<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participante extends Model
{
    use HasFactory;

    protected $table = 'participantes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'dni',
        'telefono',
        'user_id'
    ];

    // Relación con User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con VentaEntrada
    public function ventaEntradas(): HasMany
    {
        return $this->hasMany(VentaEntrada::class, 'participante_id');
    }

    // Relación con Valoracion
    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class, 'participante_id');
    }
}