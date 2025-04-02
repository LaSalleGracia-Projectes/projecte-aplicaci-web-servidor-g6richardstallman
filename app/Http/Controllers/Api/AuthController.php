<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;
use App\Mail\RegistroConfirmacion;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActualizacionPassword;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Verificar si hay un token activo en el header
        if ($request->bearerToken()) {
            return response()->json([
                'error' => 'Sesión activa',
                'message' => 'Ya existe una sesión activa. Cierre sesión antes de registrar un nuevo usuario.',
                'status' => 'error'
            ], 403);
        }

        try {
            // Validar datos de entrada
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido1' => 'required|string|max:255',
                'apellido2' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|in:organizador,participante',
                'nombre_organizacion' => 'required_if:role,organizador|string|max:255',
                'telefono_contacto' => 'required_if:role,organizador|string|max:15',
                'dni' => 'required_if:role,participante|string|max:10|unique:participante,dni',
                'telefono' => 'required_if:role,participante|string|max:15',
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'apellido1.required' => 'El primer apellido es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'email.unique' => 'Este email ya está registrado',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                'role.required' => 'El rol es obligatorio',
                'role.in' => 'El rol debe ser organizador o participante',
                'dni.required_if' => 'El DNI es obligatorio para participantes',
                'dni.unique' => 'Este DNI ya está registrado',
                'telefono.required_if' => 'El teléfono es obligatorio para participantes',
                'nombre_organizacion.required_if' => 'El nombre de la organización es obligatorio para organizadores',
                'telefono_contacto.required_if' => 'El teléfono de contacto es obligatorio para organizadores'
            ]);

            $user = User::create([
                'nombre' => $validated['nombre'],
                'apellido1' => $validated['apellido1'],
                'apellido2' => $validated['apellido2'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role']
            ]);

            // Enviar correo de confirmación
            Mail::to($user->email)->send(new RegistroConfirmacion($user));

            // Eliminar tokens existentes (por si acaso)
            $user->tokens()->delete();
            
            // Crear un token con nombre fijo para que siempre sea el mismo
            $token = $user->createToken('persistent_token')->plainTextToken;
            $user->remember_token = $token;
            $user->save();

            if ($validated['role'] === 'organizador') {
                Organizador::create([
                    'nombre_organizacion' => $validated['nombre_organizacion'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'user_id' => $user->idUser
                ]);
            } elseif ($validated['role'] === 'participante') {
                Participante::create([
                    'dni' => $validated['dni'],
                    'telefono' => $validated['telefono'],
                    'idUser' => $user->idUser
                ]);
            }

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en el registro: '.$e->getMessage());
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'password.required' => 'La contraseña es obligatoria'
            ]);

            // Buscar usuario por email
            $user = User::where('email', $validated['email'])->first();
            
            // Si no existe el usuario o la contraseña es incorrecta
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'error' => 'Credenciales incorrectas',
                    'message' => 'El email o la contraseña son incorrectos',
                    'status' => 'error'
                ], 401);
            }

            // Eliminar cualquier token existente para permitir nuevo login
            $user->tokens()->delete();
            
            // Crear token nuevo
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Guardar el token para referencia futura
            $user->remember_token = $token;
            $user->save();

            return response()->json([
                'message' => 'Login exitoso',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'status' => 'success'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en el login: '.$e->getMessage());
            
            return response()->json([
                'error' => 'Error en el login',
                'message' => 'No se pudo iniciar sesión. Por favor, inténtelo de nuevo.',
                'status' => 'error'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Verificar si hay un token de acceso
        if (!$request->bearerToken()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No hay sesión activa para cerrar',
                'status' => 'error'
            ], 401);
        }
        
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            
            // Si el usuario es válido, cerrar su sesión
            if ($user) {
                $user->tokens()->delete();
                return response()->json([
                    'message' => 'Sesión cerrada correctamente',
                    'status' => 'success'
                ], 200);
            }
            
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Token no válido',
                'status' => 'error'
            ], 401);
            
        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al cerrar sesión',
                'message' => 'No se pudo cerrar la sesión correctamente',
                'status' => 'error'
            ], 500);
        }
    }
    
    public function resetPassword(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'email' => 'required|email',
                'identificador' => 'required|string' // Puede ser DNI o teléfono de contacto
            ]);

            // Buscar el usuario por email
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'No se encontró ningún usuario con ese correo electrónico'
                ], 404);
            }

            $identificadorValido = false;

            // Verificar según el rol del usuario
            if ($user->role === 'participante') {
                // Para participantes, verificamos el DNI
                $participante = Participante::where('idUser', $user->idUser)
                                          ->where('dni', $validated['identificador'])
                                          ->first();
                
                $identificadorValido = ($participante !== null);
            } 
            elseif ($user->role === 'organizador') {
                // Para organizadores, verificamos el teléfono de contacto
                $organizador = Organizador::where('user_id', $user->idUser)
                                        ->where('telefono_contacto', $validated['identificador'])
                                        ->first();
                
                $identificadorValido = ($organizador !== null);
            }

            if (!$identificadorValido) {
                return response()->json([
                    'message' => 'El identificador proporcionado no coincide con el usuario'
                ], 400);
            }

            // Generar una nueva contraseña aleatoria
            $newPassword = Str::random(10);
            
            // Actualizar la contraseña del usuario
            $user->password = Hash::make($newPassword);
            $user->save();

            // Enviar correo electrónico con la nueva contraseña
            Mail::to($user->email)->send(new ActualizacionPassword($user, $newPassword));

            // Devolver la nueva contraseña
            return response()->json([
                'message' => 'Contraseña restablecida con éxito. Se ha enviado un correo electrónico con los detalles.',
                'password' => $newPassword
            ]);

        } catch (\Exception $e) {
            Log::error('Error en resetPassword: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al restablecer la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }

            $profileData = [
                'id' => $user->idUser,
                'nombre' => $user->nombre,
                'apellido1' => $user->apellido1,
                'apellido2' => $user->apellido2,
                'email' => $user->email,
                'role' => $user->role,
                'tipo_usuario' => $user->role === 'participante' ? 'Participante' : 'Organizador'
            ];

            // Añadir datos específicos según el rol
            if ($user->role === 'participante') {
                $participante = Participante::where('idUser', $user->idUser)->first();
                if ($participante) {
                    $profileData['dni'] = $participante->dni;
                    $profileData['telefono'] = $participante->telefono;
                }
            } elseif ($user->role === 'organizador') {
                $organizador = Organizador::where('user_id', $user->idUser)->first();
                if ($organizador) {
                    $profileData['nombre_organizacion'] = $organizador->nombre_organizacion;
                    $profileData['telefono_contacto'] = $organizador->telefono_contacto;
                }
            }

            return response()->json([
                'message' => 'Perfil recuperado con éxito',
                'data' => $profileData,
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener perfil: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener el perfil',
                'message' => 'No se pudo recuperar la información del perfil',
                'status' => 'error'
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }

            // Validar los datos de entrada
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|string|same:new_password'
            ], [
                'current_password.required' => 'La contraseña actual es obligatoria',
                'new_password.required' => 'La nueva contraseña es obligatoria',
                'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
                'new_password.different' => 'La nueva contraseña debe ser diferente a la actual',
                'confirm_password.required' => 'La confirmación de contraseña es obligatoria',
                'confirm_password.same' => 'Las contraseñas no coinciden'
            ]);

            // Verificar que la contraseña actual es correcta
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'error' => 'Contraseña incorrecta',
                    'message' => 'La contraseña actual no es correcta',
                    'status' => 'error'
                ], 400);
            }

            // Verificar que la nueva contraseña es diferente a la actual
            if ($validated['current_password'] === $validated['new_password']) {
                return response()->json([
                    'error' => 'Contraseña inválida',
                    'message' => 'La nueva contraseña debe ser diferente a la actual',
                    'status' => 'error'
                ], 400);
            }

            // Actualizar la contraseña
            $user->password = Hash::make($validated['new_password']);
            $user->save();

            // Opcional: revocar todos los tokens para forzar un nuevo login
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Contraseña actualizada con éxito',
                'status' => 'success'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al cambiar contraseña: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cambiar la contraseña',
                'message' => 'No se pudo actualizar la contraseña',
                'status' => 'error'
            ], 500);
        }
    }
} 