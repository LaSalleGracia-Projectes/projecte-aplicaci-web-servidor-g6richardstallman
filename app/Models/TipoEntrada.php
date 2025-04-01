<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEntrada extends Model
{
    use HasFactory;

    protected $table = 'tipos_entrada';
    protected $primaryKey = 'idTipoEntrada';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria'  // Los pondrá el organizador cuando cree el evento (distintos tipos de entradas)
    ];

    // Relación con Entrada
    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'tipo_entrada_id', 'idTipoEntrada');
    }
} 