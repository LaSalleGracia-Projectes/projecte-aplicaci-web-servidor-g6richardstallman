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
        'idOrganizador'
    ];

    public function organizador(): BelongsTo
    {
        return $this->belongsTo(Organizador::class, 'idOrganizador', 'idOrganizador');
    }

    public function entradas(): HasMany
    {
        return $this->hasMany(Entrada::class, 'idEvento', 'idEvento');
    }
}