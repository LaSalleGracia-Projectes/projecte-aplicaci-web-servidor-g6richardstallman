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
    public function redirectToGoogle(Request $request)
    {
        try {
            // Usar la URL de redirección configurada en el .env
            $url = Socialite::driver('google')
                ->stateless()
                ->with(['prompt' => 'select_account'])
                ->redirect()
                ->getTargetUrl();
                
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

            // Verificar si el usuario ya existe
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Si el usuario existe, actualizar sus datos de Google y autenticarlo
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar
                ]);

                // Crear token de acceso
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Usuario autenticado exitosamente',
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'needs_registration' => false
                ]);
            } else {
                // Si el usuario no existe, devolver los datos para registro
                return response()->json([
                    'message' => 'Usuario no registrado',
                    'needs_registration' => true,
                    'user_data' => [
                        'email' => $googleUser->email,
                        'nombre' => $nombre,
                        'apellido1' => $apellido1,
                        'apellido2' => $apellido2,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar
                    ]
                ], 200);
            }

        } catch (Exception $e) {
            Log::error('Error en callback de Google: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al procesar la autenticación con Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manejar la autenticación móvil con Google
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleMobile(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validated = $request->validate([
                'email' => 'required|email',
                'nombre' => 'required|string',
                'apellido1' => 'required|string',
                'apellido2' => 'nullable|string',
                'photo_url' => 'nullable|string',    
                'token' => 'nullable|string',        
                'google_id' => 'nullable|string',   
                'id' => 'nullable|string'           
            ]);

            // Verificar si el usuario ya existe
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                // Si el usuario no existe, crearlo
                $user = User::create([
                    'email' => $validated['email'],
                    'nombre' => $validated['nombre'],
                    'apellido1' => $validated['apellido1'],
                    'apellido2' => $validated['apellido2'],
                    'avatar' => $validated['photo_url'] ?? null,
                    'password' => bcrypt(Str::random(16)),
                    'google_id' => $validated['google_id'] ?? $validated['id'] ?? null,
                    'role' => 'participante'
                ]);

                // Crear token de acceso
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Usuario registrado y autenticado exitosamente',
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'needs_registration' => true
                ]);
            } else {
                // Si el usuario existe, actualizar sus datos de Google
                $user->update([
                    'google_id' => $validated['google_id'] ?? $validated['id'] ?? null,
                    'avatar' => $validated['photo_url'] ?? null
                ]);

                // Crear token de acceso
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Usuario autenticado exitosamente',
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'needs_registration' => false
                ]);
            }

        } catch (Exception $e) {
            Log::error('Error en autenticación móvil de Google: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al procesar la autenticación con Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manejar la autenticación móvil con Google incluyendo datos adicionales del usuario
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToGoogleMobile(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validated = $request->validate([
                'email' => 'required|email',
                'nombre' => 'required|string',
                'apellido1' => 'required|string',
                'apellido2' => 'nullable|string',
                'photo_url' => 'nullable|string',    // Este campo se mapea a avatar
                'token' => 'nullable|string',        // Token de Google (se almacena en Sanctum)
                'google_id' => 'nullable|string',    // ID de Google
                'id' => 'nullable|string'            // ID adicional
            ]);

            // Verificar si el usuario ya existe
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                // Si el usuario no existe, crearlo
                $user = User::create([
                    'email' => $validated['email'],
                    'nombre' => $validated['nombre'],
                    'apellido1' => $validated['apellido1'],
                    'apellido2' => $validated['apellido2'],
                    'avatar' => $validated['photo_url'] ?? null,
                    'google_id' => $validated['google_id'] ?? $validated['id'] ?? null,
                    'role' => 'participante'
                ]);
            } else {
                // Si el usuario existe, actualizar sus datos de Google
                $user->update([
                    'google_id' => $validated['google_id'] ?? $validated['id'] ?? null,
                    'avatar' => $validated['photo_url'] ?? null
                ]);
            }

            // Generar token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => $user->wasRecentlyCreated ? 'Usuario registrado y autenticado exitosamente' : 'Usuario autenticado exitosamente',
                'token' => $token,
                'user' => $user,
                'role' => $user->role
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la autenticación con Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completar el registro de un usuario de Google
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeGoogleRegistration(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'nombre' => 'required|string',
                'apellido1' => 'required|string',
                'apellido2' => 'nullable|string',
                'google_id' => 'required|string',
                'avatar' => 'nullable|string'
            ]);

            // Crear el usuario
            $user = User::create([
                'email' => $validated['email'],
                'nombre' => $validated['nombre'],
                'apellido1' => $validated['apellido1'],
                'apellido2' => $validated['apellido2'],
                'password' => bcrypt(Str::random(16)),
                'google_id' => $validated['google_id'],
                'avatar' => $validated['avatar'],
                'email_verified_at' => now(),
                'role' => 'participante'
            ]);

            // Crear token de acceso
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario registrado y autenticado exitosamente',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error al completar registro de Google: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al completar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 