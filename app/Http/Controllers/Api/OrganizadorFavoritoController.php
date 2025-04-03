<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrganizadorFavoritoController extends Controller
{
    /**
     * Obtener organizadores favoritos del participante autenticado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizadoresFavoritos(Request $request)
    {
        try {
            // Verificar que el usuario es un participante
            $user = $request->user();
            if ($user->role !== 'participante') {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden tener organizadores favoritos',
                    'status' => 'error'
                ], 403);
            }

            // Obtener el participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            if (!$participante) {
                return response()->json([
                    'error' => 'No encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }

            // Obtener organizadores favoritos con información del usuario
            $favoritos = $participante->organizadoresFavoritos()
                ->with(['user:idUser,nombre,apellido1,apellido2,email,avatar'])
                ->get()
                ->map(function ($organizador) {
                    return [
                        'id' => $organizador->idOrganizador,
                        'nombre_organizacion' => $organizador->nombre_organizacion,
                        'telefono_contacto' => $organizador->telefono_contacto,
                        'user' => [
                            'id' => $organizador->user->idUser,
                            'nombre' => $organizador->user->nombre,
                            'apellido1' => $organizador->user->apellido1,
                            'apellido2' => $organizador->user->apellido2,
                            'email' => $organizador->user->email,
                            'avatar' => $organizador->user->avatar
                        ],
                        'is_favorite' => true
                    ];
                });

            // Verificar si no hay favoritos
            if ($favoritos->isEmpty()) {
                return response()->json([
                    'message' => 'No tienes organizadores favoritos',
                    'favoritos' => [],
                    'status' => 'success'
                ]);
            }

            return response()->json([
                'message' => 'Organizadores favoritos obtenidos exitosamente',
                'favoritos' => $favoritos,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener organizadores favoritos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno',
                'message' => 'No se pudieron obtener los organizadores favoritos: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Añadir un organizador a favoritos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOrganizadorFavorito(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'idOrganizador' => 'required|exists:organizador,idOrganizador'
            ]);

            // Verificar que el usuario es un participante
            $user = $request->user();
            if ($user->role !== 'participante') {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden añadir organizadores a favoritos',
                    'status' => 'error'
                ], 403);
            }

            // Obtener el participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            if (!$participante) {
                return response()->json([
                    'error' => 'No encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }

            // Verificar si el organizador ya está en favoritos
            $yaEsFavorito = DB::table('organizador_favorito')
                ->where('idParticipante', $participante->idParticipante)
                ->where('idOrganizador', $validated['idOrganizador'])
                ->exists();

            if ($yaEsFavorito) {
                return response()->json([
                    'message' => 'El organizador ya está en favoritos',
                    'status' => 'success'
                ]);
            }

            // Añadir a favoritos
            $participante->organizadoresFavoritos()->attach($validated['idOrganizador']);

            // Obtener el organizador para devolverlo en la respuesta
            $organizador = Organizador::with('user:idUser,nombre,apellido1,apellido2,email,avatar')
                ->find($validated['idOrganizador']);

            return response()->json([
                'message' => 'Organizador añadido a favoritos',
                'organizador' => [
                    'id' => $organizador->idOrganizador,
                    'nombre_organizacion' => $organizador->nombre_organizacion,
                    'telefono_contacto' => $organizador->telefono_contacto,
                    'user' => [
                        'id' => $organizador->user->idUser,
                        'nombre' => $organizador->user->nombre,
                        'apellido1' => $organizador->user->apellido1,
                        'apellido2' => $organizador->user->apellido2,
                        'email' => $organizador->user->email,
                        'avatar' => $organizador->user->avatar
                    ],
                    'is_favorite' => true
                ],
                'status' => 'success'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al añadir organizador a favoritos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno',
                'message' => 'No se pudo añadir el organizador a favoritos: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Eliminar un organizador de favoritos
     *
     * @param  int  $idOrganizador
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeOrganizadorFavorito($idOrganizador, Request $request)
    {
        try {
            // Verificar que el usuario es un participante
            $user = $request->user();
            if ($user->role !== 'participante') {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden eliminar organizadores de favoritos',
                    'status' => 'error'
                ], 403);
            }

            // Obtener el participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            if (!$participante) {
                return response()->json([
                    'error' => 'No encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }

            // Verificar si el organizador existe y está en favoritos
            $esFavorito = DB::table('organizador_favorito')
                ->where('idParticipante', $participante->idParticipante)
                ->where('idOrganizador', $idOrganizador)
                ->exists();

            if (!$esFavorito) {
                return response()->json([
                    'error' => 'No encontrado',
                    'message' => 'El organizador no está en tus favoritos',
                    'status' => 'error'
                ], 404);
            }

            // Eliminar de favoritos
            $participante->organizadoresFavoritos()->detach($idOrganizador);

            return response()->json([
                'message' => 'Organizador eliminado de favoritos',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar organizador de favoritos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno',
                'message' => 'No se pudo eliminar el organizador de favoritos: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Verificar si un organizador está en favoritos
     *
     * @param  int  $idOrganizador
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOrganizadorFavorito($idOrganizador, Request $request)
    {
        try {
            // Verificar que el usuario es un participante
            $user = $request->user();
            if ($user->role !== 'participante') {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden verificar organizadores favoritos',
                    'status' => 'error'
                ], 403);
            }

            // Obtener el participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            if (!$participante) {
                return response()->json([
                    'error' => 'No encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }

            // Verificar si es favorito
            $esFavorito = DB::table('organizador_favorito')
                ->where('idParticipante', $participante->idParticipante)
                ->where('idOrganizador', $idOrganizador)
                ->exists();

            return response()->json([
                'is_favorite' => $esFavorito,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar organizador favorito: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno',
                'message' => 'No se pudo verificar si el organizador está en favoritos: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 