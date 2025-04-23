<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organizador;
use Illuminate\Http\Request;

class OrganizadorController extends Controller
{
    /**
     * Obtener todos los organizadores
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOrganizadores()
    {
        try {
            $organizadores = Organizador::with('user:idUser,avatar')->get();
            
            // Transformar la respuesta para incluir solo los campos necesarios
            $organizadoresSimplificados = $organizadores->map(function ($organizador) {
                return [
                    'idOrganizador' => $organizador->idOrganizador,
                    'nombre_organizacion' => $organizador->nombre_organizacion,
                    'avatar' => $organizador->user->avatar ?? null
                ];
            });
            
            return response()->json([
                'organizadores' => $organizadoresSimplificados,
                'message' => 'Organizadores obtenidos correctamente',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los organizadores',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 