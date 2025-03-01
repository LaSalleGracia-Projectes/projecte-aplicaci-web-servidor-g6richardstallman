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

class AuthController extends Controller
{
    public function register(Request $request)
    {
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
                'message' => 'Usuario registrado correctamente',
                'user' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en el registro: '.$e->getMessage());
            return response()->json([
                'error' => 'Error en el registro',
                'message' => 'No se pudo completar el registro. Por favor, inténtelo de nuevo.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        // Verificar si hay un token activo en el header
        if ($request->bearerToken()) {
            return response()->json([
                'error' => 'Sesión activa',
                'message' => 'Ya existe una sesión activa. Cierre sesión antes de iniciar una nueva.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'password.required' => 'La contraseña es obligatoria'
            ]);

            if (!auth()->attempt($validated)) {
                return response()->json([
                    'error' => 'Credenciales incorrectas',
                    'message' => 'El email o la contraseña son incorrectos'
                ], 401);
            }

            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login exitoso',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en el login: '.$e->getMessage());
            return response()->json([
                'error' => 'Error en el login',
                'message' => 'No se pudo iniciar sesión. Por favor, inténtelo de nuevo.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Verificar si hay un token activo
        if (!$request->bearerToken()) {
            return response()->json([
                'message' => 'No hay sesión activa'
            ], 200);
        }

        try {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            
            if (!$token) {
                return response()->json([
                    'message' => 'No hay sesión activa'
                ], 200);
            }

            $token->delete();
            
            return response()->json([
                'message' => 'Sesión cerrada correctamente'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error en el logout: '.$e->getMessage());
            return response()->json([
                'message' => 'No hay sesión activa'
            ], 200);
        }
    }
} 