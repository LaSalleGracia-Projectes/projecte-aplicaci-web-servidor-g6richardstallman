<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'admin') {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            return $next($request);
        });
    }

    public function getAllUsers()
    {
        try {
            $users = User::select('idUser', 'nombre', 'apellido1', 'apellido2', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Usuarios obtenidos exitosamente',
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changeUserPassword(Request $request, $userId)
    {
        try {
            // Validar la nueva contraseña
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ], [
                'new_password.required' => 'La nueva contraseña es obligatoria',
                'new_password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'new_password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar el usuario
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Cambiar la contraseña
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada exitosamente',
                'user' => [
                    'idUser' => $user->idUser,
                    'email' => $user->email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cambiar la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 