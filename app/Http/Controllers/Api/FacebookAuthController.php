<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class FacebookAuthController extends Controller
{
    /**
     * Redireccionar al usuario a Facebook
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToFacebook()
    {
        try { 
            $url = Socialite::driver('facebook')
                ->stateless()
                ->scopes(['email'])
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'url' => $url
            ]);
        } catch (Exception $e) {
            Log::error('Error al redireccionar a Facebook: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al conectar con Facebook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la información del usuario de Facebook y autenticarlo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            
            // Extraer nombre y apellidos
            $nombreCompleto = explode(' ', $facebookUser->name);
            $nombre = $nombreCompleto[0];
            $apellido1 = isset($nombreCompleto[1]) ? $nombreCompleto[1] : '';
            $apellido2 = isset($nombreCompleto[2]) ? $nombreCompleto[2] : '';

            // Generar contraseña aleatoria
            $password = bcrypt(Str::random(16));

            // Buscar o crear usuario
            $user = User::updateOrCreate(
                ['email' => $facebookUser->email],
                [
                    'nombre' => $nombre,
                    'apellido1' => $apellido1,
                    'apellido2' => $apellido2,
                    'password' => $password,
                    'facebook_id' => $facebookUser->id,
                    'avatar' => $facebookUser->avatar,
                    'role' => 'participante'
                ]
            );

            // Crear token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario autenticado exitosamente',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (Exception $e) {
            Log::error('Error en callback de Facebook: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al procesar la autenticación con Facebook',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 