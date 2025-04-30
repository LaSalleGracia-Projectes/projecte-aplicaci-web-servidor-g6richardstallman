<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participante extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'participante';
    
    // Clave primaria
    protected $primaryKey = 'idParticipante';
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = true;

    // Campos que se pueden asignar 
    protected $fillable = [
        'dni',
        'telefono',
        'idUser',
        'direccion'
    ];

    // Relación con User
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

    // Relación con Favoritos
    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class, 'idParticipante', 'idParticipante');
    }

    // Relación con Eventos a través de Favoritos
    public function eventosFavoritos()
    {
        return Evento::whereIn('idEvento', $this->favoritos()->pluck('idEvento'));
    }

    // Añadir una relación muchos a muchos con Organizador para los favoritos
    public function organizadoresFavoritos()
    {
        return $this->belongsToMany(
            Organizador::class, 
            'organizador_favorito', 
            'idParticipante', 
            'idOrganizador'
        )->withTimestamps();
    }

    // Método para obtener el avatar del usuario
    public function getAvatarAttribute()
    {
        return $this->user ? $this->user->avatar : null;
    }
}