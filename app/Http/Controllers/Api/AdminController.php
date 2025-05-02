<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
} 