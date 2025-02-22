<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pago';
    protected $primaryKey = 'idPago';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email'
    ];
}