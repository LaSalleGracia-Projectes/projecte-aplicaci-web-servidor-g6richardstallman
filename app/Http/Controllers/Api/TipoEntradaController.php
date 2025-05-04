<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoEntrada;
use App\Models\Evento;
use App\Models\Organizador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TipoEntradaController extends Controller
{
    public function store(Request $request, $idEvento)
    {
        try {
            // Obtener el organizador del usuario actual
            $user = $request->user();
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            
            if (!$organizador) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Solo los organizadores pueden crear tipos de entrada',
                    'status' => 'error'
                ], 403);
            }
            
            // Verificar que el evento existe y pertenece al organizador actual
            $evento = Evento::where('idEvento', $idEvento)
                          ->where('idOrganizador', $organizador->idOrganizador)
                          ->first();

            if (!$evento) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'El evento no existe o no tienes permisos para modificarlo',
                    'status' => 'error'
                ], 403);
            }

            // Validar los datos de entrada
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'precio' => 'required|numeric|min:0',
                'cantidad_disponible' => 'nullable|integer|min:1',
                'descripcion' => 'nullable|string',
                'es_ilimitado' => 'required|boolean'
            ], [
                'nombre.required' => 'El nombre del tipo de entrada es obligatorio',
                'nombre.max' => 'El nombre no puede tener más de 100 caracteres',
                'precio.required' => 'El precio es obligatorio',
                'precio.numeric' => 'El precio debe ser un número',
                'precio.min' => 'El precio no puede ser negativo',
                'cantidad_disponible.integer' => 'La cantidad debe ser un número entero',
                'cantidad_disponible.min' => 'La cantidad debe ser mayor a 0',
                'es_ilimitado.required' => 'Debe especificar si las entradas son ilimitadas',
                'es_ilimitado.boolean' => 'El campo es_ilimitado debe ser verdadero o falso'
            ]);

            // Si es ilimitado, la cantidad_disponible debe ser null
            if ($validated['es_ilimitado']) {
                $validated['cantidad_disponible'] = null;
            } else {
                // Si no es ilimitado, la cantidad_disponible es obligatoria
                if (!isset($validated['cantidad_disponible'])) {
                    return response()->json([
                        'error' => 'Validación fallida',
                        'message' => 'La cantidad de entradas disponibles es obligatoria para entradas limitadas',
                        'status' => 'error'
                    ], 422);
                }
            }

            // Crear el tipo de entrada
            $tipoEntrada = TipoEntrada::create([
                'idEvento' => $idEvento,
                'nombre' => $validated['nombre'],
                'precio' => $validated['precio'],
                'cantidad_disponible' => $validated['cantidad_disponible'],
                'entradas_vendidas' => 0,
                'descripcion' => $validated['descripcion'] ?? null,
                'es_ilimitado' => $validated['es_ilimitado'],
                'activo' => true
            ]);

            return response()->json([
                'message' => 'Tipo de entrada creado con éxito',
                'data' => $tipoEntrada,
                'status' => 'success'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear tipo de entrada: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al crear el tipo de entrada',
                'message' => 'No se pudo crear el tipo de entrada',
                'status' => 'error'
            ], 500);
        }
    }

    public function update(Request $request, $idEvento, $idTipoEntrada)
    {
        try {
            // Obtener el organizador del usuario actual
            $user = $request->user();
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            
            if (!$organizador) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Solo los organizadores pueden modificar tipos de entrada',
                    'status' => 'error'
                ], 403);
            }
            
            // Verificar que el tipo de entrada existe y pertenece al evento del organizador
            $tipoEntrada = TipoEntrada::join('evento', 'tipo_entrada.idEvento', '=', 'evento.idEvento')
                ->where('tipo_entrada.idTipoEntrada', $idTipoEntrada)
                ->where('tipo_entrada.idEvento', $idEvento)
                ->where('evento.idOrganizador', $organizador->idOrganizador)
                ->select('tipo_entrada.*')
                ->first();

            if (!$tipoEntrada) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'El tipo de entrada no existe o no tienes permisos para modificarlo',
                    'status' => 'error'
                ], 403);
            }

            // Validar los datos de entrada
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:100',
                'precio' => 'sometimes|numeric|min:0',
                'cantidad_disponible' => 'nullable|integer|min:1',
                'descripcion' => 'nullable|string',
                'es_ilimitado' => 'sometimes|boolean',
                'activo' => 'sometimes|boolean'
            ]);

            // Si se está cambiando a ilimitado
            if (isset($validated['es_ilimitado']) && $validated['es_ilimitado']) {
                $validated['cantidad_disponible'] = null;
            } elseif (isset($validated['es_ilimitado']) && !$validated['es_ilimitado']) {
                // Si se está cambiando a limitado, requerir cantidad
                if (!isset($validated['cantidad_disponible'])) {
                    return response()->json([
                        'error' => 'Validación fallida',
                        'message' => 'La cantidad de entradas disponibles es obligatoria para entradas limitadas',
                        'status' => 'error'
                    ], 422);
                }
            }

            // Actualizar el tipo de entrada
            $tipoEntrada->update($validated);

            return response()->json([
                'message' => 'Tipo de entrada actualizado con éxito',
                'data' => $tipoEntrada,
                'status' => 'success'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar tipo de entrada: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al actualizar el tipo de entrada',
                'message' => 'No se pudo actualizar el tipo de entrada',
                'status' => 'error'
            ], 500);
        }
    }

    public function destroy(Request $request, $idEvento, $idTipoEntrada)
    {
        try {
            // Obtener el organizador del usuario actual
            $user = $request->user();
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            
            if (!$organizador) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Solo los organizadores pueden eliminar tipos de entrada',
                    'status' => 'error'
                ], 403);
            }
            
            // Verificar que el tipo de entrada existe y pertenece al evento del organizador
            $tipoEntrada = TipoEntrada::join('evento', 'tipo_entrada.idEvento', '=', 'evento.idEvento')
                ->where('tipo_entrada.idTipoEntrada', $idTipoEntrada)
                ->where('tipo_entrada.idEvento', $idEvento)
                ->where('evento.idOrganizador', $organizador->idOrganizador)
                ->select('tipo_entrada.*')
                ->first();

            if (!$tipoEntrada) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'El tipo de entrada no existe o no tienes permisos para eliminarlo',
                    'status' => 'error'
                ], 403);
            }

            // Si ya se han vendido entradas, no permitir eliminar
            if ($tipoEntrada->entradas_vendidas > 0) {
                return response()->json([
                    'error' => 'Operación no permitida',
                    'message' => 'No se puede eliminar un tipo de entrada que ya tiene ventas',
                    'status' => 'error'
                ], 400);
            }

            // Eliminar el tipo de entrada
            $tipoEntrada->delete();

            return response()->json([
                'message' => 'Tipo de entrada eliminado con éxito',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar tipo de entrada: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al eliminar el tipo de entrada',
                'message' => 'No se pudo eliminar el tipo de entrada',
                'status' => 'error'
            ], 500);
        }
    }

    public function index($idEvento)
    {
        try {
            // Cargar el evento con las relaciones necesarias
            $evento = Evento::with(['tiposEntrada', 'organizador', 'organizador.organizador'])->findOrFail($idEvento);

            // Extraer los tipos de entrada
            $tiposEntrada = $evento->tiposEntrada->map(function ($tipo) {
                // Calcular disponibilidad si no es ilimitado
                $disponibilidad = $tipo->es_ilimitado ? null : ($tipo->cantidad_disponible - $tipo->entradas_vendidas);
                $tipoData = $tipo->toArray();
                $tipoData['disponibilidad'] = $disponibilidad;
                return $tipoData;
            });

            return response()->json([
                'message' => 'Datos del evento y tipos de entrada recuperados con éxito',
                'evento' => $evento,
                'tipos_entrada' => $tiposEntrada,
                'status' => 'success'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Evento no encontrado',
                'message' => 'No se encontró el evento con el ID proporcionado.',
                'status' => 'error'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al recuperar tipos de entrada y organizador: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => 'Ocurrió un error al recuperar la información.',
                'status' => 'error'
            ], 500);
        }
    }

    public function getEventoDetalle(Request $request, $idEvento)
    {
        try {
            // Cargar el evento con todas sus relaciones necesarias
            $evento = Evento::with(['tiposEntrada', 'organizador'])->findOrFail($idEvento);
            
            // Verificar si el evento es favorito del usuario (falso por defecto)
            $isFavorito = false;
            if ($request->user()) {
                // Implementación para verificar favoritos cuando sea necesario
                // $isFavorito = $request->user()->favoritos()->where('idEvento', $idEvento)->exists();
            }
            
            // Obtener los tipos de entrada con el formato exacto solicitado
            $tiposEntrada = $evento->tiposEntrada->map(function ($tipo) {
                return [
                    'id' => $tipo->idTipoEntrada,
                    'nombre' => $tipo->nombre,
                    'precio' => $tipo->precio,
                    'descripcion' => $tipo->descripcion ?? '',
                    'cantidadDisponible' => $tipo->es_ilimitado ? null : $tipo->cantidad_disponible,
                    'entradasVendidas' => $tipo->entradas_vendidas,
                    'esIlimitado' => (bool)$tipo->es_ilimitado
                ];
            });
            
            // Preparar datos del organizador con el formato solicitado
            $organizadorData = null;
            if ($evento->organizador) {
                $organizadorData = [
                    'id' => $evento->organizador->idUser,
                    'nombre' => $evento->organizador->nombre ?? 'Desconocido',
                    'user' => [
                        'id' => $evento->organizador->idUser,
                        'nombre' => $evento->organizador->nombre,
                        'email' => $evento->organizador->email
                    ]
                ];
            }
            
            // Preparar la respuesta exactamente como la solicitada
            // Usamos los nombres correctos según la estructura de la base de datos
            $response = [
                'evento' => [
                    'id' => $evento->idEvento,
                    'titulo' => $evento->nombreEvento,
                    'descripcion' => $evento->descripcion,
                    'fechaEvento' => $evento->fechaEvento ?? '',
                    'hora' => $evento->horaEvento ?? '',
                    'ubicacion' => $evento->ubicacion,
                    'categoria' => $evento->categoria,
                    'imagen' => $evento->imagen ?? 'eventos/default.jpg',
                    'esOnline' => (bool)$evento->es_online,
                    'isFavorito' => $isFavorito,
                    'organizador' => $organizadorData,
                    'tipos_entrada' => $tiposEntrada
                ],
                'status' => 'success'
            ];
            
            return response()->json($response, 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Evento no encontrado: ' . $e->getMessage());
            return response()->json([
                'error' => 'Evento no encontrado',
                'message' => 'No se encontró el evento con el ID proporcionado.',
                'status' => 'error'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al recuperar detalles del evento: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => 'Ocurrió un error al recuperar la información del evento.',
                'status' => 'error'
            ], 500);
        }
    }
} 