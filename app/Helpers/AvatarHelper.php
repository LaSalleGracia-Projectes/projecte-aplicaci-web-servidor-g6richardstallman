<?php

namespace App\Helpers;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class AvatarHelper
{
    /**
     * Genera una URL de avatar basada en el nombre para participantes
     *
     * @param string $nombre
     * @param string $apellido1
     * @return string
     */
    public static function generateParticipantAvatarUrl($nombre, $apellido1)
    {
        $initials = mb_substr($nombre, 0, 1) . mb_substr($apellido1, 0, 1);
        $initials = urlencode(strtoupper($initials));
        return "https://ui-avatars.com/api/?name={$initials}&background=random&color=fff&bold=true&format=png";
    }

    /**
     * Genera una URL de avatar basada en el nombre de la organización
     *
     * @param string $nombreOrganizacion
     * @return string
     */
    public static function generateOrganizationAvatarUrl($nombreOrganizacion)
    {
        $words = explode(' ', $nombreOrganizacion);
        $initials = '';
        
        // Tomar la primera letra de cada palabra, máximo 2 letras
        foreach ($words as $word) {
            if (strlen($initials) < 2) {
                $initials .= mb_substr($word, 0, 1);
            }
        }
        
        $initials = urlencode(strtoupper($initials));
        return "https://ui-avatars.com/api/?name={$initials}&background=random&color=fff&bold=true&format=png";
    }

    /**
     * Genera una URL de avatar por defecto usando ui-avatars.com y guarda la imagen
     *
     * @param string $name
     * @param string $role
     * @return string
     */
    public static function generateDefaultAvatarUrl($name, $role = null)
    {
        $backgroundColor = self::getBackgroundColorByRole($role);
        $name = urlencode($name);
        
        // Generar URL de ui-avatars.com
        $avatarUrl = "https://ui-avatars.com/api/?name={$name}&background={$backgroundColor}&color=fff&bold=true&format=png";
        
        // Descargar la imagen
        $imageContents = file_get_contents($avatarUrl);
        
        // Generar nombre único para el archivo
        $filename = Str::random(40) . '.png';
        
        // Guardar la imagen en storage/app/public/user/avatar
        \Storage::put('public/user/avatar/' . $filename, $imageContents);
        
        // Devolver la ruta relativa para la base de datos
        return '/storage/user/avatar/' . $filename;
    }

    /**
     * Obtiene el color de fondo según el rol
     *
     * @param string|null $role
     * @return string
     */
    private static function getBackgroundColorByRole($role)
    {
        $colors = [
            'admin' => '1a237e', // Azul oscuro
            'organizador' => '2e7d32', // Verde
            'participante' => 'c62828', // Rojo
            'default' => '424242' // Gris
        ];

        return $colors[$role] ?? $colors['default'];
    }

    public function generateAvatar($name, $role)
    {
        // Crear una imagen en blanco
        $img = Image::canvas(200, 200, $this->getBackgroundColor($role));
        
        // Obtener las iniciales
        $initials = $this->getInitials($name);
        
        // Configurar el texto
        $img->text($initials, 100, 100, function($font) {
            $font->file(public_path('fonts/Roboto-Bold.ttf'));
            $font->size(80);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Convertir a PNG y devolver el contenido
        return $img->encode('png');
    }
    
    private function getBackgroundColor($role)
    {
        // Colores según el rol
        $colors = [
            'admin' => '#1a237e', // Azul oscuro
            'organizador' => '#2e7d32', // Verde
            'participante' => '#c62828', // Rojo
            'default' => '#424242' // Gris
        ];
        
        return $colors[$role] ?? $colors['default'];
    }
    
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }
} 