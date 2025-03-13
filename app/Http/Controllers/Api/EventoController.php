<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Participante;
use App\Models\Organizador;

class EventoController extends Controller
{
    /**
     * Obtener todos los eventos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllEventos()
    {
        try {
            // Obtener todos los eventos con sus relaciones
            $eventos = Evento::with([
                'organizador', 
                'organizador.user',
                'entradas'
            ])->get();

            // Transformar los datos para la respuesta
            $eventosData = $eventos->map(function ($evento) {
                return [
                    'id' => $evento->idEvento,
                    'nombreEvento' => $evento->nombreEvento,
                    'fechaEvento' => $evento->fechaEvento,
                    'descripcion' => $evento->descripcion,
                    'hora' => $evento->hora,
                    'ubicacion' => $evento->ubicacion,
                    'imagen' => $evento->imagen,
                    'categoria' => $evento->categoria,
                    'lugar' => $evento->lugar,
                    'organizador' => [
                        'id' => $evento->organizador->idOrganizador,
                        'nombre_organizacion' => $evento->organizador->nombre_organizacion,
                        'telefono_contacto' => $evento->organizador->telefono_contacto,
                        'user' => [
                            'id' => $evento->organizador->user->idUser,
                            'nombre' => $evento->organizador->user->nombre,
                            'apellido1' => $evento->organizador->user->apellido1,
                            'apellido2' => $evento->organizador->user->apellido2,
                            'email' => $evento->organizador->user->email
                        ]
                    ],
                    'entradas' => $evento->entradas->map(function ($entrada) {
                        return [
                            'id' => $entrada->idEntrada,
                            'tipo' => $entrada->tipo,
                            'precio' => $entrada->precio,
                            'cantidad_disponible' => $entrada->cantidad_disponible
                        ];
                    })
                ];
            });

            return response()->json([
                'message' => 'Eventos obtenidos con éxito',
                'eventos' => $eventosData
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener eventos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los eventos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un evento específico por su ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventoById($id)
    {
        try {
            // Buscar el evento con sus relaciones
            $evento = Evento::with([
                'organizador', 
                'organizador.user',
                'entradas'
            ])->find($id);

            // Si no se encuentra el evento
            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            // Verificar si el evento es favorito del usuario actual
            $isFavorito = false;
            if (Auth::check()) {
                $user = Auth::user();
                $participante = Participante::where('idUser', $user->idUser)->first();
                if ($participante) {
                    $isFavorito = $evento->esFavoritoDe($participante->idParticipante);
                }
            }

            // Transformar los datos para la respuesta
            $eventoData = [
                'id' => $evento->idEvento,
                'nombreEvento' => $evento->nombreEvento,
                'fechaEvento' => $evento->fechaEvento,
                'descripcion' => $evento->descripcion,
                'hora' => $evento->hora,
                'ubicacion' => $evento->ubicacion,
                'imagen' => $evento->imagen,
                'categoria' => $evento->categoria,
                'lugar' => $evento->lugar,
                'isFavorito' => $isFavorito,
                'organizador' => [
                    'id' => $evento->organizador->idOrganizador,
                    'nombre_organizacion' => $evento->organizador->nombre_organizacion,
                    'telefono_contacto' => $evento->organizador->telefono_contacto,
                    'user' => [
                        'id' => $evento->organizador->user->idUser,
                        'nombre' => $evento->organizador->user->nombre,
                        'apellido1' => $evento->organizador->user->apellido1,
                        'apellido2' => $evento->organizador->user->apellido2,
                        'email' => $evento->organizador->user->email
                    ]
                ],
                'entradas' => $evento->entradas->map(function ($entrada) {
                    return [
                        'id' => $entrada->idEntrada,
                        'tipo' => $entrada->tipo,
                        'precio' => $entrada->precio,
                        'cantidad_disponible' => $entrada->cantidad_disponible
                    ];
                })
            ];

            return response()->json([
                'message' => 'Evento obtenido con éxito',
                'evento' => $eventoData
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener evento por ID: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un evento
    public function deleteEvento($id)
    {
        try {
            // Buscar el evento
            $evento = Evento::findOrFail($id);
            
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar que el usuario es un organizador
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            if (!$organizador) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los organizadores pueden eliminar eventos',
                    'code' => 'UNAUTHORIZED_ROLE',
                    'status' => 'error'
                ], 403);
            }

            // Verificar que el organizador autenticado es el dueño del evento
            if ($evento->idOrganizador !== $organizador->idOrganizador) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'No tienes permiso para eliminar este evento',
                    'code' => 'UNAUTHORIZED_EVENT',
                    'status' => 'error'
                ], 403);
            }

            // Eliminar el evento
            $evento->delete();

            return response()->json([
                'message' => 'Evento eliminado correctamente',
                'code' => 'DELETED_SUCCESS',
                'status' => 'success'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Evento no encontrado',
                'message' => 'El evento que intentas eliminar no existe',
                'code' => 'EVENT_NOT_FOUND',
                'status' => 'error'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar evento: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al eliminar el evento',
                'message' => 'No se pudo eliminar el evento. Por favor, inténtelo de nuevo.',
                'code' => 'DELETE_ERROR',
                'status' => 'error',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    // Crear un nuevo evento
    public function createEvento(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar que el usuario es un organizador
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            if (!$organizador) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los organizadores pueden crear eventos',
                    'code' => 'UNAUTHORIZED_ROLE',
                    'status' => 'error'
                ], 403);
            }

            // Validar los datos del evento
            $validated = $request->validate([
                'nombreEvento' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fechaEvento' => 'required|date|after:today',
                'hora' => 'required|date_format:H:i',
                'ubicacion' => 'required|string|max:255',
                'lugar' => 'required|string|max:255',
                'categoria' => 'required|string|max:50',
                'imagen' => 'nullable|string'
            ]);

            // Crear el evento
            $evento = new Evento();
            $evento->nombreEvento = $validated['nombreEvento'];
            $evento->descripcion = $validated['descripcion'];
            $evento->fechaEvento = $validated['fechaEvento'];
            $evento->hora = $validated['hora'];
            $evento->ubicacion = $validated['ubicacion'];
            $evento->lugar = $validated['lugar'];
            $evento->categoria = $validated['categoria'];
            $evento->imagen = $validated['imagen'] ?? null;
            $evento->idOrganizador = $organizador->idOrganizador;

            $evento->save();

            return response()->json([
                'message' => 'Evento creado correctamente',
                'evento' => $evento,
                'status' => 'success'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear evento: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al crear el evento',
                'message' => 'No se pudo crear el evento. Por favor, inténtelo de nuevo.',
                'status' => 'error'
            ], 500);
        }
    }

    // Actualizar un evento existente
    public function updateEvento(Request $request, $id)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar que el usuario es un organizador
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            if (!$organizador) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los organizadores pueden modificar eventos',
                    'code' => 'UNAUTHORIZED_ROLE',
                    'status' => 'error'
                ], 403);
            }

            try {
                // Buscar el evento
                $evento = Evento::findOrFail($id);
                
                // Verificar que el organizador autenticado es el dueño del evento
                if ($evento->idOrganizador !== $organizador->idOrganizador) {
                    return response()->json([
                        'error' => 'Acceso denegado',
                        'message' => 'No tienes permiso para modificar este evento',
                        'code' => 'UNAUTHORIZED_EVENT',
                        'status' => 'error'
                    ], 403);
                }

                // Validar los datos del evento
                $validated = $request->validate([
                    'nombreEvento' => 'required|string|max:255',
                    'descripcion' => 'required|string',
                    'fechaEvento' => 'required|date|after:today',
                    'hora' => 'required|date_format:H:i',
                    'ubicacion' => 'required|string|max:255',
                    'lugar' => 'required|string|max:255',
                    'categoria' => 'required|string|max:50',
                    'imagen' => 'nullable|string'
                ]);

                // Actualizar el evento
                $evento->update($validated);

                return response()->json([
                    'message' => 'Evento actualizado correctamente',
                    'evento' => $evento,
                    'status' => 'success'
                ], 200);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'error' => 'Error de validación',
                    'messages' => $e->errors(),
                    'status' => 'error'
                ], 422);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'Evento no encontrado',
                    'message' => 'El evento que intentas actualizar no existe',
                    'code' => 'EVENT_NOT_FOUND',
                    'status' => 'error'
                ], 404);
            } catch (\Exception $e) {
                Log::error('Error al actualizar evento: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Error al actualizar el evento',
                    'message' => 'No se pudo actualizar el evento. Por favor, inténtelo de nuevo.',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar evento: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al actualizar el evento',
                'message' => 'No se pudo actualizar el evento. Por favor, inténtelo de nuevo.',
                'status' => 'error'
            ], 500);
        }
    }
} 