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
            $organizadores = Organizador::with('user')->get();
            return response()->json([
                'organizadores' => $organizadores,
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

    /**
     * Obtener detalles de un organizador específico por ID
     *
     * @param int $id ID del organizador
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizadorById($id)
    {
        try {
            $organizador = Organizador::with('user')->findOrFail($id);
            
            // Transformar la respuesta para ajustarla al formato requerido
            $organizadorData = [
                'id' => $organizador->idOrganizador,
                'nombre_organizacion' => $organizador->nombre_organizacion,
                'telefono_contacto' => $organizador->telefono_contacto,
                'user' => $organizador->user,
                'avatar_url' => $organizador->user && $organizador->user->avatar 
                    ? url('/storage/' . $organizador->user->avatar) 
                    : url('/storage/avatars/default_avatar.png')
            ];
            
            return response()->json([
                'organizador' => $organizadorData,
                'message' => 'Organizador obtenido correctamente',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el organizador',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Obtener eventos de un organizador específico
     *
     * @param int $id ID del organizador
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventosByOrganizador($id)
    {
        try {
            $organizador = Organizador::findOrFail($id);
            
            // Cargar eventos con sus tipos de entrada
            $eventos = $organizador->eventos()
                ->with('tiposEntrada')
                ->orderBy('fechaEvento', 'asc')
                ->get();
            
            // Transformar para añadir URL completa de las imágenes
            $eventosTransformados = $eventos->map(function($evento) {
                return [
                    'id' => $evento->idEvento,
                    'nombreEvento' => $evento->nombreEvento,
                    'fechaEvento' => $evento->fechaEvento,
                    'descripcion' => $evento->descripcion,
                    'hora' => $evento->hora,
                    'ubicacion' => $evento->ubicacion,
                    'imagen' => $evento->imagen,
                    'imagen_url' => url('/storage/' . $evento->imagen),
                    'categoria' => $evento->categoria,
                    'lugar' => $evento->lugar,
                    'tipos_entrada' => $evento->tiposEntrada->map(function ($tipo) {
                        return [
                            'id' => $tipo->idTipoEntrada,
                            'nombre' => $tipo->nombre,
                            'precio' => $tipo->precio,
                            'cantidad_disponible' => $tipo->cantidad_disponible,
                            'es_ilimitado' => $tipo->es_ilimitado
                        ];
                    })
                ];
            });
            
            return response()->json([
                'organizador' => [
                    'id' => $organizador->idOrganizador,
                    'nombre_organizacion' => $organizador->nombre_organizacion
                ],
                'eventos' => $eventosTransformados,
                'total' => count($eventos),
                'message' => 'Eventos obtenidos correctamente',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los eventos del organizador',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 