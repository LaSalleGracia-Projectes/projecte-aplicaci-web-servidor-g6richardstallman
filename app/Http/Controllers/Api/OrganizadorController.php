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
    public function getAllOrganizadores(Request $request)
    {
        try {
            $organizadores = Organizador::with('user')->get();
            
            // Verificar si hay usuario autenticado para comprobar favoritos
            $user = $request->user();
            $participante = null;
            if ($user && $user->role === 'participante') {
                $participante = $user->participante;
            }
            
            // Transformar organizadores añadiendo información de favoritos
            $organizadoresData = $organizadores->map(function($organizador) use ($participante) {
                // Verificar si es favorito
                $esFavorito = false;
                if ($participante) {
                    $esFavorito = $participante->organizadoresFavoritos()
                        ->where('idOrganizador', $organizador->idOrganizador)
                        ->exists();
                }
                
                // Obtener nombre completo del usuario
                $nombreUsuario = null;
                if ($organizador->user) {
                    $nombreUsuario = $organizador->user->nombre;
                    if ($organizador->user->apellido1) {
                        $nombreUsuario .= ' ' . $organizador->user->apellido1;
                    }
                    if ($organizador->user->apellido2) {
                        $nombreUsuario .= ' ' . $organizador->user->apellido2;
                    }
                }
                
                return [
                    'id' => $organizador->idOrganizador,
                    'nombre_organizacion' => $organizador->nombre_organizacion,
                    'telefono_contacto' => $organizador->telefono_contacto,
                    'nombre_usuario' => $nombreUsuario,
                    'user' => $organizador->user,
                    'avatar_url' => $organizador->user && $organizador->user->avatar 
                        ? url('/storage/' . $organizador->user->avatar) 
                        : url('/storage/avatars/default_avatar.png'),
                    'is_favorite' => $esFavorito
                ];
            });
            
            return response()->json([
                'organizadores' => $organizadoresData,
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
    public function getOrganizadorById($id, Request $request)
    {
        try {
            $organizador = Organizador::with('user')->findOrFail($id);
            
            // Obtenemos el nombre completo del usuario asociado
            $nombreUsuario = null;
            if ($organizador->user) {
                $nombreUsuario = $organizador->user->nombre;
                if ($organizador->user->apellido1) {
                    $nombreUsuario .= ' ' . $organizador->user->apellido1;
                }
                if ($organizador->user->apellido2) {
                    $nombreUsuario .= ' ' . $organizador->user->apellido2;
                }
            }
            
            // Comprobar si es favorito para el usuario autenticado
            $esFavorito = false;
            $user = $request->user();
            if ($user && $user->role === 'participante') {
                $participante = $user->participante;
                if ($participante) {
                    $esFavorito = $participante->organizadoresFavoritos()
                        ->where('idOrganizador', $id)
                        ->exists();
                }
            }
            
            // Transformar la respuesta para ajustarla al formato requerido
            $organizadorData = [
                'id' => $organizador->idOrganizador,
                'nombre_organizacion' => $organizador->nombre_organizacion,
                'telefono_contacto' => $organizador->telefono_contacto,
                'nombre_usuario' => $nombreUsuario,
                'user' => $organizador->user,
                'avatar_url' => $organizador->user && $organizador->user->avatar 
                    ? url('/storage/' . $organizador->user->avatar) 
                    : url('/storage/avatars/default_avatar.png'),
                'is_favorite' => $esFavorito
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

    /**
     * Verifica si un organizador es favorito del participante autenticado
     *
     * @param int $idOrganizador ID del organizador a verificar
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIsFavorito($idOrganizador, Request $request)
    {
        try {
            // Verificar que el usuario está autenticado
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'is_favorite' => false,
                    'message' => 'Usuario no autenticado',
                    'status' => 'success'
                ]);
            }

            // Si no es un participante, no puede tener favoritos
            if ($user->role !== 'participante') {
                return response()->json([
                    'is_favorite' => false,
                    'message' => 'Solo los participantes pueden tener favoritos',
                    'status' => 'success'
                ]);
            }

            // Obtener el participante
            $participante = $user->participante;
            if (!$participante) {
                return response()->json([
                    'is_favorite' => false,
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'success'
                ]);
            }

            // Verificar si el organizador es favorito
            $esFavorito = $participante->organizadoresFavoritos()
                ->where('idOrganizador', $idOrganizador)
                ->exists();

            return response()->json([
                'is_favorite' => $esFavorito,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar favorito',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 