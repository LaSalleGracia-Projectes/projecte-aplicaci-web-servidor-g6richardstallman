<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'idUser'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relación con Organizador
    public function organizador()
    {
        return $this->hasOne(Organizador::class, 'user_id', 'idUser');
    }
    
    // Relación con Participante
    public function participante()
    {
        return $this->hasOne(Participante::class, 'idUser', 'idUser');
    }
}