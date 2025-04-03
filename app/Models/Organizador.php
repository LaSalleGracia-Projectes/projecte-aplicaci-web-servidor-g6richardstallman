<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    // Añadir una relación muchos a muchos con Participante para los favoritos
    public function participantesFavoritos()
    {
        return $this->belongsToMany(
            Participante::class, 
            'organizador_favorito', 
            'idOrganizador', 
            'idParticipante'
        )->withTimestamps();
    }

    // Método para verificar si un participante ha marcado al organizador como favorito
    public function isFavorite($idParticipante)
    {
        return $this->participantesFavoritos()->where('idParticipante', $idParticipante)->exists();
    }
}