<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organizador extends Model
{
    use HasFactory;

    protected $table = 'organizador';
    protected $primaryKey = 'idOrganizador';
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = true;

    protected $fillable = [
        'nombre_organizacion',
        'telefono_contacto',
        'user_id'
    ];

    // Relación con User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'idUser');
    }

    // Relación con Evento
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'idOrganizador', 'idOrganizador');
    }
}