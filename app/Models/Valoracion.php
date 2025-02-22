<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Valoracion extends Model
{
    use HasFactory;

    protected $table = 'valoraciones';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'puntuacion',
        'comentario',
        'evento_id',
        'participante_id'
    ];
}