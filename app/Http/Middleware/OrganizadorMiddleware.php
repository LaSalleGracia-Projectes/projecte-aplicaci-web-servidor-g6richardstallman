<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organizador;

class OrganizadorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'No autenticado',
                'message' => 'Debe iniciar sesión para realizar esta acción',
                'status' => 'error'
            ], 401);
        }

        $user = Auth::user();
        
        if ($user->role !== 'organizador') {
            return response()->json([
                'error' => 'Acceso denegado',
                'message' => 'Solo los organizadores pueden realizar esta acción',
                'status' => 'error'
            ], 403);
        }

        // Verificar si existe el registro de organizador
        $organizador = Organizador::where('user_id', $user->idUser)->first();
        if (!$organizador) {
            return response()->json([
                'error' => 'Perfil incompleto',
                'message' => 'No se encontró el perfil de organizador',
                'status' => 'error'
            ], 403);
        }

        return $next($request);
    }
}