<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:organizador,participante',
            'dni' => 'required_if:role,participante|string|max:10|unique:participante,dni',
            'telefono' => 'required_if:role,participante|string|max:15',
        ]);

        try {
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
                    'nombre_organizacion' => 'Organización de ' . $validated['nombre'],
                    'telefono_contacto' => '000000000',
                    'user_id' => $user->idUser  // Nota: usando user_id según el modelo Organizador
                ]);
            } elseif ($validated['role'] === 'participante') {
                Participante::create([
                    'dni' => $validated['dni'],
                    'telefono' => $validated['telefono'],
                    'idUser' => $user->idUser
                ]);
            }

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en el registro: '.$e->getMessage());
            return response()->json([
                'error' => 'Error inesperado en el registro',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 