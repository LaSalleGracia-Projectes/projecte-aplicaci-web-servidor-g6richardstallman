<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear directorio para avatares si no existe
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }

        // Crear un avatar por defecto usando una imagen generada por código
        $defaultAvatarPath = 'avatars/default_avatar.png';
        
        // Verificar si ya existe el avatar por defecto
        if (!Storage::disk('public')->exists($defaultAvatarPath)) {
            // Generar una imagen simple como avatar por defecto
            $image = imagecreatetruecolor(200, 200);
            
            // Colores
            $bgColor = imagecolorallocate($image, 70, 130, 180); // Azul acero
            $textColor = imagecolorallocate($image, 255, 255, 255); // Blanco
            
            // Rellenar el fondo
            imagefill($image, 0, 0, $bgColor);
            
            // Agregar texto "USUARIO"
            $text = "USER";
            $font = 5; // Usar una fuente predefinida
            
            // Centrar el texto
            $textWidth = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            $x = (200 - $textWidth) / 2;
            $y = (200 - $textHeight) / 2;
            
            // Dibujar el texto
            imagestring($image, $font, $x, $y, $text, $textColor);
            
            // Guardar la imagen
            $tempFile = tempnam(sys_get_temp_dir(), 'avatar');
            imagepng($image, $tempFile);
            imagedestroy($image);
            
            // Mover al storage público
            Storage::disk('public')->put($defaultAvatarPath, file_get_contents($tempFile));
            unlink($tempFile);
        }

        // Actualizar la configuración para apuntar al avatar por defecto
        DB::table('users')
            ->whereNull('avatar')
            ->update(['avatar' => $defaultAvatarPath]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar el avatar por defecto para no afectar a usuarios existentes
        // Solo revertir si es necesario
    }
}; 