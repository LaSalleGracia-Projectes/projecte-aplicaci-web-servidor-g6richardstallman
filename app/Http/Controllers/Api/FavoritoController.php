<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorito;
use App\Models\Participante;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoritoController extends Controller
{
    /**
     * Obtener todos los eventos favoritos de un participante
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavoritos()
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            // Verificar si el usuario es un participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            
            if (!$participante) {
                return response()->json([
                    'message' => 'Solo los participantes pueden tener eventos favoritos'
                ], 403);
            }
            
            // Obtener los eventos favoritos con sus relaciones
            $favoritos = Favorito::with(['evento', 'evento.organizador'])
                ->where('idParticipante', $participante->idParticipante)
                ->get();
            
            // Transformar los datos para la respuesta
            $eventosData = $favoritos->map(function ($favorito) {
                $evento = $favorito->evento;
                if (!$evento) {
                    return null; // Manejar el caso donde el evento ya no existe
                }
                
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
                    'fechaAgregado' => $favorito->fechaAgregado,
                    'organizador' => $evento->organizador ? [
                        'id' => $evento->organizador->idOrganizador,
                        'nombre_organizacion' => $evento->organizador->nombre_organizacion
                    ] : null
                ];
            })->filter(); // Eliminar los valores nulos
            
            return response()->json([
                'message' => 'Eventos favoritos obtenidos con éxito',
                'favoritos' => $eventosData
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener favoritos: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al obtener los eventos favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Añadir un evento a favoritos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFavorito(Request $request)
    {
        try {
            Log::info('Iniciando añadir favorito');
            Log::info('Request data:', $request->all());
            
            // Validar la solicitud
            $request->validate([
                'idEvento' => 'required|exists:evento,idEvento'
            ]);
            
            // Obtener el usuario autenticado
            $user = Auth::user();
            Log::info('Usuario autenticado:', ['id' => $user->idUser, 'email' => $user->email, 'role' => $user->role]);
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }
            
            // Verificar si el usuario es un participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            
            if (!$participante) {
                Log::warning('Usuario no es participante', ['user_id' => $user->idUser, 'role' => $user->role]);
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden añadir eventos a favoritos',
                    'status' => 'error'
                ], 403);
            }
            
            Log::info('Participante encontrado', ['participante_id' => $participante->idParticipante]);
            
            // Verificar si el evento ya está en favoritos
            $existeFavorito = Favorito::where('idParticipante', $participante->idParticipante)
                                    ->where('idEvento', $request->idEvento)
                                    ->exists();
            
            if ($existeFavorito) {
                return response()->json([
                    'message' => 'Este evento ya está en tus favoritos',
                    'status' => 'info'
                ], 200);
            }
            
            // Crear el nuevo favorito
            $favorito = new Favorito();
            $favorito->idParticipante = $participante->idParticipante;
            $favorito->idEvento = $request->idEvento;
            $favorito->fechaAgregado = now();
            $favorito->save();
            
            Log::info('Favorito creado exitosamente', ['favorito_id' => $favorito->id]);
            
            return response()->json([
                'message' => 'Evento añadido a favoritos con éxito',
                'favorito' => $favorito,
                'status' => 'success'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al añadir favorito', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al añadir favorito: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error interno',
                'message' => 'No se pudo añadir el evento a favoritos. Por favor, inténtelo de nuevo.',
                'debug' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    
    /**
     * Eliminar un evento de favoritos
     *
     * @param int $idEvento
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFavorito($idEvento)
    {
        try {
            Log::info('Iniciando eliminar favorito', ['idEvento' => $idEvento]);
            
            // Obtener el usuario autenticado
            $user = Auth::user();
            Log::info('Usuario autenticado:', ['id' => $user->idUser, 'email' => $user->email, 'role' => $user->role]);
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }
            
            // Verificar si el usuario es un participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            
            if (!$participante) {
                Log::warning('Usuario no es participante', ['user_id' => $user->idUser, 'role' => $user->role]);
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden eliminar eventos de favoritos',
                    'status' => 'error'
                ], 403);
            }
            
            Log::info('Participante encontrado', ['participante_id' => $participante->idParticipante]);
            
            // Buscar el favorito
            $favorito = Favorito::where('idParticipante', $participante->idParticipante)
                              ->where('idEvento', $idEvento)
                              ->first();
            
            if (!$favorito) {
                return response()->json([
                    'message' => 'Este evento no está en tus favoritos',
                    'status' => 'info'
                ], 200);
            }
            
            // Eliminar el favorito
            $favorito->delete();
            Log::info('Favorito eliminado exitosamente');
            
            return response()->json([
                'message' => 'Evento eliminado de favoritos con éxito',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar favorito: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error interno',
                'message' => 'No se pudo eliminar el evento de favoritos. Por favor, inténtelo de nuevo.',
                'debug' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    
    /**
     * Verificar si un evento está en favoritos
     *
     * @param int $idEvento
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkFavorito($idEvento)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado',
                    'isFavorito' => false
                ], 401);
            }
            
            // Verificar si el usuario es un participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            
            if (!$participante) {
                return response()->json([
                    'message' => 'Solo los participantes pueden tener eventos favoritos',
                    'isFavorito' => false
                ]);
            }
            
            // Verificar si el evento está en favoritos
            $isFavorito = Favorito::where('idParticipante', $participante->idParticipante)
                                 ->where('idEvento', $idEvento)
                                 ->exists();
            
            return response()->json([
                'isFavorito' => $isFavorito
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar favorito: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al verificar si el evento está en favoritos',
                'error' => $e->getMessage(),
                'isFavorito' => false
            ], 500);
        }
    }
} 