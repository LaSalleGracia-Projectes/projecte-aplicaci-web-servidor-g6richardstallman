<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Newsletter extends Model
{
    protected $table = 'newsletter';
    protected $primaryKey = 'idNewsletter';
    public $timestamps = false;

    protected $fillable = [
        'spotNotification',
        'fechaEvento',
        'contenido',
        'idEvento'
    ];

    // Relación con Evento
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'idEvento');
    }
}