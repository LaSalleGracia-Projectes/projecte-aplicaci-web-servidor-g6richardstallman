<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evento';
    protected $primaryKey = 'idEvento';
    public $timestamps = true;

    protected $fillable = [
        'nombreEvento',
        'fechaEvento',
        'descripcion',
        'hora',
        'ubicacion',
        'imagen',
        'categoria',
        'lugar',
        'idOrganizador',
        'es_online'
    ];

    public function organizador(): BelongsTo
    {
        return $this->belongsTo(Organizador::class, 'idOrganizador', 'idOrganizador');
    }

    public function entradas(): HasMany
    {
        return $this->hasMany(Entrada::class, 'idEvento', 'idEvento');
    }

    // Relación para tipos de entrada
    public function tiposEntrada(): HasMany
    {
        return $this->hasMany(TipoEntrada::class, 'idEvento', 'idEvento');
    }

    // Nueva relación para favoritos
    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class, 'idEvento', 'idEvento');
    }
    
    // Método para verificar si un evento es favorito de un participante
    public function esFavoritoDe($idParticipante): bool
    {
        return $this->favoritos()->where('idParticipante', $idParticipante)->exists();
    }
}