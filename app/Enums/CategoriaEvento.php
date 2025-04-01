<?php

namespace App\Enums;

enum CategoriaEvento: string
{
    case Concierto = 'Concierto';
    case Festival = 'Festival';
    case Teatro = 'Teatro';
    case Deportes = 'Deportes';
    case Conferencia = 'Conferencia';
    case Exposicion = 'Exposición';
    case Taller = 'Taller';
    case Otro = 'Otro';

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value'); 
    }

    public static function getForValidation(): string
    {
        return implode(',', self::getAllValues());
    }
}