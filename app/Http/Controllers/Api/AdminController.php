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

    public function updateUser(Request $request, $userId)
    {
        try {
            // Buscar el usuario
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'apellido1' => 'sometimes|required|string|max:255',
                'apellido2' => 'nullable|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $userId . ',idUser',
                'role' => 'sometimes|required|in:admin,organizador,participante',
                'password' => 'sometimes|required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.string' => 'El nombre debe ser texto',
                'nombre.max' => 'El nombre no puede tener más de 255 caracteres',
                'apellido1.required' => 'El primer apellido es obligatorio',
                'apellido1.string' => 'El primer apellido debe ser texto',
                'apellido1.max' => 'El primer apellido no puede tener más de 255 caracteres',
                'apellido2.string' => 'El segundo apellido debe ser texto',
                'apellido2.max' => 'El segundo apellido no puede tener más de 255 caracteres',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'email.unique' => 'Este email ya está registrado',
                'role.required' => 'El rol es obligatorio',
                'role.in' => 'El rol debe ser admin, organizador o participante',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar solo los campos proporcionados
            $updateData = $request->only(['nombre', 'apellido1', 'apellido2', 'email', 'role']);
            
            // Si se proporciona una nueva contraseña, hashearla
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user' => [
                    'idUser' => $user->idUser,
                    'nombre' => $user->nombre,
                    'apellido1' => $user->apellido1,
                    'apellido2' => $user->apellido2,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 