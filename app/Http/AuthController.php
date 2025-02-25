<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:organizador,participante',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role']
        ]);

        if ($validated['role'] === 'organizador') {
            Organizador::create([
                'nombre_organizacion' => 'Organizacion de ' . $validated['name'],
                'telefono_contacto' => '000000000',
                'user_id' => $user->idUser
            ]);
        } else {
            Participante::create([
                'dni' => '00000000X',
                'telefono' => '000000000',
                'idUser' => $user->idUser
            ]);
        }

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'error' => 'Error de validación',
            'messages' => $e->errors()
        ], 422);
    } catch (\Illuminate\Database\QueryException $e) {
        dd($e); 
        return response()->json([
            'error' => 'Error en la base de datos',
            'message' => $e->getMessage()
        ], 500);
    } catch (\Exception $e) {
        dd($e); 
        return response()->json([
            'error' => 'Error inesperado',
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTrace()
        ], 500);
    }
}
}