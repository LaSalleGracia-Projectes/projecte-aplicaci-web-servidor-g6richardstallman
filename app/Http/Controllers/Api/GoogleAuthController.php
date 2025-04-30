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
use Google_Client;
use App\Helpers\AvatarHelper;

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
            Log::error('Error al redireccionar a Google : ' . $e->getMessage());
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

            // Generar avatar si no viene de Google
            $avatar = $googleUser->avatar;
            if (!$avatar) {
                $avatar = AvatarHelper::generateParticipantAvatarUrl($nombre, $apellido1);
            }

            // Buscar o crear el usuario con los campos que coinciden con tu modelo User
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'nombre' => $nombre,
                    'apellido1' => $apellido1,
                    'apellido2' => $apellido2,
                    'password' => $password,
                    'google_id' => $googleUser->id,
                    'avatar' => $avatar,
                    'email_verified_at' => now(),
                    'role' => 'participante'
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

    /**
     * Verificar el idToken de Google recibido desde la app móvil.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyGoogleToken(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->input('id_token');
        $clientId = config('services.google.client_id');

        if (!$clientId) {
             Log::error('Google Client ID no configurado en .env o services.php');
             return response()->json(['message' => 'Error de configuración del servidor'], 500);
        }

        try {
            $client = new Google_Client(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($idToken);

            if ($payload) {
                $googleUserId = $payload['sub'];
                $email = $payload['email'];
                $nombre = $payload['given_name'] ?? '';
                $apellidos = $payload['family_name'] ?? '';
                $avatarGoogle = $payload['picture'] ?? null;

                $apellidosArray = explode(' ', $apellidos, 2);
                $apellido1 = $apellidosArray[0] ?? '';
                $apellido2 = $apellidosArray[1] ?? '';

                // Usar avatar de Google o generar uno si no existe
                $avatar = $avatarGoogle ?: AvatarHelper::generateParticipantAvatarUrl($nombre, $apellido1);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'nombre' => $nombre,
                        'apellido1' => $apellido1,
                        'apellido2' => $apellido2,
                        'password' => User::where('email', $email)->doesntExist() ? bcrypt(Str::random(16)) : User::where('email', $email)->first()->password,
                        'google_id' => $googleUserId,
                        'avatar' => $avatar,
                        'email_verified_at' => now(),
                        'role' => 'participante'
                    ]
                );

                $token = $user->createToken('auth_token_google')->plainTextToken;

                Log::info('Usuario autenticado/creado vía Google Token: ' . $user->email);

                return response()->json([
                    'message' => 'Autenticación con Google exitosa',
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]);

            } else {
                Log::warning('Intento de verificación de Google Token inválido');
                return response()->json(['message' => 'Token de Google inválido'], 401);
            }
        } catch (Exception $e) {
            Log::error('Error al verificar Google Token: ' . $e->getMessage());
            return response()->json(['message' => 'Error al verificar el token'], 500);
        }
    }
} 