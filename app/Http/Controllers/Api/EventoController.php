<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Participante;

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

    // Otros métodos del controlador...
} 