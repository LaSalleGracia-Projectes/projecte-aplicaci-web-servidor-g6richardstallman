<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redireccionar al usuario a la página de autenticación de Google
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToGoogle()
    {
        try {
            $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
            return response()->json([
                'url' => $url
            ]);
        } catch (Exception $e) {
            Log::error('Error al redireccionar a Google: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al conectar con Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la información del usuario de Google y autenticarlo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Extraer el nombre y apellidos del nombre completo de Google
            $nombreCompleto = explode(' ', $googleUser->name);
            $nombre = $nombreCompleto[0];
            $apellido1 = isset($nombreCompleto[1]) ? $nombreCompleto[1] : '';
            $apellido2 = isset($nombreCompleto[2]) ? $nombreCompleto[2] : '';

            // Generar una contraseña aleatoria segura
            $password = bcrypt(Str::random(16));

            // Buscar o crear el usuario con los campos que coinciden con tu modelo User
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'nombre' => $nombre,
                    'apellido1' => $apellido1,
                    'apellido2' => $apellido2,
                    'password' => $password, // Añadimos la contraseña
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(),
                    'role' => 'participante' // Asignamos rol por defecto
                ]
            );

            // Crear token de acceso
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario autenticado exitosamente',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (Exception $e) {
            Log::error('Error en callback de Google: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al procesar la autenticación con Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 