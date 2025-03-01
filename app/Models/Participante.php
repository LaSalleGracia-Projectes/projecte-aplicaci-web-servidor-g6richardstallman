<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'participante';
    protected $primaryKey = 'idParticipante';
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = true;

    protected $fillable = [
        'dni',
        'telefono',
        'idUser'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUser', 'idUser');
    }

    public function ventaEntradas(): HasMany
    {
        return $this->hasMany(VentaEntrada::class, 'idParticipante', 'idParticipante');
    }
    
    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class, 'idParticipante', 'idParticipante');
    }
}