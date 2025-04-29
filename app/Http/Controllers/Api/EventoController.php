<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Participante;
use App\Models\Organizador;
use App\Models\TipoEntrada;

class EventoController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializeStorage();
    }

    /**
     * Inicializa el almacenamiento para las imágenes
     */
    private function initializeStorage()
    {
        try {
            // Verificar si existe el directorio para eventos
            $storagePath = storage_path('app/public/eventos');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                Log::info('Directorio de almacenamiento creado en inicialización', ['path' => $storagePath]);
            }

            // Verificar si existe la imagen por defecto
            $defaultImagePath = storage_path('app/public/eventos/default.jpg');
            if (!file_exists($defaultImagePath)) {
                // Copiar imagen por defecto desde recursos públicos
                $publicDefaultImage = public_path('img/default-event.jpg');
                if (file_exists($publicDefaultImage)) {
                    copy($publicDefaultImage, $defaultImagePath);
                    Log::info('Imagen por defecto copiada en inicialización');
                } else {
                    // Crear una imagen por defecto básica
                    $img = imagecreatetruecolor(800, 600);
                    $backgroundColor = imagecolorallocate($img, 200, 200, 200);
                    $textColor = imagecolorallocate($img, 0, 0, 0);
                    imagefill($img, 0, 0, $backgroundColor);
                    imagestring($img, 5, 320, 300, 'Evento sin imagen', $textColor);
                    imagejpeg($img, $defaultImagePath);
                    imagedestroy($img);
                    Log::info('Imagen por defecto creada en inicialización');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al inicializar almacenamiento: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los eventos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllEventos()
    {
        try {
            // Obtener todos los eventos con sus relaciones y ordenarlos por fecha
            $eventos = Evento::with([
                'organizador', 
                'organizador.user',
                'entradas'
            ])
            ->orderBy('fechaEvento', 'asc') // Ordenar por fecha (ascendente - próximos primero)
            ->get();

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
                    'imagen_url' => url('/storage/' . $evento->imagen), // Añadir URL completa
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
            Log::info('Iniciando creación de evento', ['request' => $request->except('imagen')]);
            
            // Validar datos de entrada
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fecha' => 'required|date|after:today',
                'hora' => 'required|date_format:H:i',
                'ubicacion' => 'required|string|max:255',
                'categoria' => 'required|string|max:100',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'es_online' => 'required|boolean',
                'tipos_entrada' => 'required|array|min:1',
                'tipos_entrada.*.nombre' => 'required|string|max:100',
                'tipos_entrada.*.precio' => 'required|numeric|min:0',
                'tipos_entrada.*.cantidad_disponible' => 'required_if:tipos_entrada.*.es_ilimitado,false|nullable|integer|min:1',
                'tipos_entrada.*.descripcion' => 'nullable|string',
                'tipos_entrada.*.es_ilimitado' => 'required|boolean'
            ], [
                'titulo.required' => 'El título es obligatorio',
                'descripcion.required' => 'La descripción es obligatoria',
                'fecha.required' => 'La fecha es obligatoria',
                'fecha.after' => 'La fecha debe ser posterior a hoy',
                'hora.required' => 'La hora es obligatoria',
                'hora.date_format' => 'El formato de hora debe ser HH:MM',
                'ubicacion.required' => 'La ubicación es obligatoria',
                'categoria.required' => 'La categoría es obligatoria',
                'imagen.image' => 'El archivo debe ser una imagen',
                'imagen.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif',
                'imagen.max' => 'La imagen no puede pesar más de 2MB',
                'es_online.required' => 'Debe especificar si el evento es online',
                'tipos_entrada.required' => 'Debe especificar al menos un tipo de entrada',
                'tipos_entrada.min' => 'Debe especificar al menos un tipo de entrada',
                'tipos_entrada.*.nombre.required' => 'El nombre del tipo de entrada es obligatorio',
                'tipos_entrada.*.precio.required' => 'El precio del tipo de entrada es obligatorio',
                'tipos_entrada.*.precio.numeric' => 'El precio debe ser un número',
                'tipos_entrada.*.precio.min' => 'El precio no puede ser negativo',
                'tipos_entrada.*.cantidad_disponible.required_if' => 'La cantidad de entradas disponibles es obligatoria para entradas limitadas',
                'tipos_entrada.*.cantidad_disponible.integer' => 'La cantidad debe ser un número entero',
                'tipos_entrada.*.cantidad_disponible.min' => 'La cantidad debe ser mayor a 0',
                'tipos_entrada.*.es_ilimitado.required' => 'Debe especificar si las entradas son ilimitadas',
                'tipos_entrada.*.es_ilimitado.boolean' => 'El campo es_ilimitado debe ser verdadero o falso'
            ]);
            
            Log::info('Validación completada con éxito', ['validated' => $validated]);

            // Obtener el organizador del usuario actual
            $user = $request->user();
            Log::info('Información del usuario', ['id' => $user->idUser, 'role' => $user->role]);
            
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            Log::info('Resultado de búsqueda de organizador', ['encontrado' => $organizador ? true : false]);
            
            if (!$organizador) {
                Log::warning('Usuario no es organizador', ['user_id' => $user->idUser]);
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Solo los organizadores pueden crear eventos',
                    'status' => 'error'
                ], 403);
            }
            
            Log::info('Organizador obtenido', ['id' => $organizador->idOrganizador]);

            DB::beginTransaction();
            try {
                Log::info('Iniciando transacción de DB');
                
                // Procesar la imagen si se proporciona
                $imagenPath = null;
                $imagenProporcionada = false;
                
                if ($request->hasFile('imagen')) {
                    $imagenProporcionada = true;
                    Log::info('Procesando imagen subida por el usuario');
                    
                    try {
                        // Verificar si existe el directorio para eventos
                        $storagePath = storage_path('app/public/eventos');
                        if (!file_exists($storagePath)) {
                            mkdir($storagePath, 0755, true);
                            Log::info('Directorio de almacenamiento creado', ['path' => $storagePath]);
                        }
                        
                        $imagenPath = $request->file('imagen')->store('eventos', 'public');
                        Log::info('Imagen guardada', ['path' => $imagenPath]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar imagen: ' . $e->getMessage());
                        DB::rollback();
                        return response()->json([
                            'error' => 'Error al guardar la imagen',
                            'message' => 'Ocurrió un problema al guardar la imagen del evento. Por favor, intente con otra imagen o más tarde.',
                            'status' => 'error'
                        ], 500);
                    }
                } else {
                    // Usar imagen por defecto
                    Log::info('No se proporcionó imagen, usando imagen por defecto');
                    $imagenPath = 'eventos/default.jpg';
                }

                // Crear el evento con los nombres de campos correctos
                Log::info('Creando evento');
                $evento = new Evento();
                $evento->nombreEvento = $validated['titulo'];
                $evento->descripcion = $validated['descripcion'];
                $evento->fechaEvento = $validated['fecha'];
                $evento->hora = $validated['hora'];
                $evento->ubicacion = $validated['ubicacion'];
                $evento->lugar = $validated['ubicacion']; // Duplicando el valor en ambos campos
                $evento->categoria = $validated['categoria'];
                $evento->imagen = $imagenPath;
                $evento->es_online = $validated['es_online'];
                $evento->idOrganizador = $organizador->idOrganizador;
                
                Log::info('Datos del evento a guardar', ['evento' => json_encode([
                    'nombreEvento' => $evento->nombreEvento,
                    'descripcion' => $evento->descripcion,
                    'fechaEvento' => $evento->fechaEvento,
                    'hora' => $evento->hora,
                    'ubicacion' => $evento->ubicacion,
                    'lugar' => $evento->lugar,
                    'categoria' => $evento->categoria,
                    'imagen' => $evento->imagen,
                    'es_online' => $evento->es_online,
                    'idOrganizador' => $evento->idOrganizador
                ])]);

                try {
                    $resultado = $evento->save();
                    Log::info('Resultado de guardar evento', ['resultado' => $resultado, 'id' => $evento->idEvento ?? 'no asignado']);
                    
                    if (!$resultado) {
                        Log::error('Error al guardar el evento: resultado falso');
                        throw new \Exception('Error al guardar el evento en la base de datos');
                    }
                } catch (\Exception $e) {
                    Log::error('Excepción al guardar evento: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                    throw new \Exception('Error al guardar el evento: ' . $e->getMessage());
                }
                
                Log::info('Evento guardado con éxito', ['id' => $evento->idEvento]);

                // Crear los tipos de entrada
                Log::info('Creando tipos de entrada', ['cantidad' => count($validated['tipos_entrada'])]);
                
                foreach ($validated['tipos_entrada'] as $index => $tipoEntrada) {
                    Log::info('Procesando tipo de entrada', ['index' => $index, 'data' => $tipoEntrada]);
                    
                    try {
                        $nuevoTipoEntrada = new TipoEntrada();
                        $nuevoTipoEntrada->idEvento = $evento->idEvento;
                        $nuevoTipoEntrada->nombre = $tipoEntrada['nombre'];
                        $nuevoTipoEntrada->precio = $tipoEntrada['precio'];
                        $nuevoTipoEntrada->cantidad_disponible = $tipoEntrada['es_ilimitado'] ? null : $tipoEntrada['cantidad_disponible'];
                        $nuevoTipoEntrada->entradas_vendidas = 0;
                        $nuevoTipoEntrada->descripcion = $tipoEntrada['descripcion'] ?? null;
                        $nuevoTipoEntrada->es_ilimitado = $tipoEntrada['es_ilimitado'];
                        $nuevoTipoEntrada->activo = true;

                        Log::info('Datos del tipo de entrada a guardar', [
                            'idEvento' => $nuevoTipoEntrada->idEvento,
                            'nombre' => $nuevoTipoEntrada->nombre,
                            'precio' => $nuevoTipoEntrada->precio,
                            'cantidad_disponible' => $nuevoTipoEntrada->cantidad_disponible,
                            'entradas_vendidas' => $nuevoTipoEntrada->entradas_vendidas,
                            'descripcion' => $nuevoTipoEntrada->descripcion,
                            'es_ilimitado' => $nuevoTipoEntrada->es_ilimitado,
                            'activo' => $nuevoTipoEntrada->activo
                        ]);
                        
                        $resultadoTipo = $nuevoTipoEntrada->save();
                        Log::info('Resultado de guardar tipo de entrada', ['resultado' => $resultadoTipo, 'id' => $nuevoTipoEntrada->idTipoEntrada ?? 'no asignado']);
                        
                        if (!$resultadoTipo) {
                            Log::error('Error al guardar tipo de entrada: resultado falso');
                            throw new \Exception('Error al guardar el tipo de entrada');
                        }
                    } catch (\Exception $e) {
                        Log::error('Excepción al guardar tipo de entrada: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                        throw new \Exception('Error al guardar tipo de entrada: ' . $e->getMessage());
                    }
                    
                    Log::info('Tipo de entrada guardado con éxito', ['id' => $nuevoTipoEntrada->idTipoEntrada ?? 'desconocido']);
                }

                DB::commit();
                Log::info('Transacción completada con éxito');

                // Cargar la relación con los tipos de entrada
                try {
                    Log::info('Intentando cargar relación tiposEntrada');
                    $evento->load('tiposEntrada');
                    Log::info('Relación tiposEntrada cargada', ['cantidad' => count($evento->tiposEntrada)]);
                } catch (\Exception $e) {
                    Log::warning('Error al cargar relación tiposEntrada: ' . $e->getMessage());
                    // Continuar aunque falle la carga de la relación
                }

                $mensaje = 'Evento creado con éxito';
                if (!$imagenProporcionada) {
                    $mensaje = 'Evento creado con éxito. Se ha utilizado una imagen por defecto.';
                }

                return response()->json([
                    'message' => $mensaje,
                    'data' => $evento,
                    'status' => 'success'
                ], 201);

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error en transacción: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación: ' . json_encode($e->errors()));
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de base de datos al crear evento: ' . $e->getMessage(), [
                'sql' => $e->getSql() ?? 'No disponible', 
                'bindings' => $e->getBindings() ?? [],
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorCode = $e->errorInfo[1] ?? 0;
            
            if ($errorCode == 1062) {
                return response()->json([
                    'error' => 'Error al crear el evento',
                    'message' => 'Ya existe un evento con ese nombre',
                    'status' => 'error'
                ], 409);
            }
            
            return response()->json([
                'error' => 'Error de base de datos',
                'message' => 'Error al guardar la información en la base de datos: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        } catch (\PDOException $e) {
            Log::error('Error PDO al crear evento: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error de conexión a la base de datos',
                'message' => 'Error al conectar con la base de datos: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error al crear evento: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al crear el evento',
                'message' => 'Se produjo un error inesperado al crear el evento: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    // Actualizar un evento existente
    public function updateEvento(Request $request, $id)
    {
        try {
            // Obtener el organizador del usuario actual
            $user = $request->user();
            $organizador = Organizador::where('user_id', $user->idUser)->first();
            
            if (!$organizador) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Solo los organizadores pueden modificar eventos',
                    'status' => 'error'
                ], 403);
            }
            
            // Verificar que el evento existe y pertenece al organizador actual
            $evento = Evento::where('idEvento', $id)
                          ->where('idOrganizador', $organizador->idOrganizador)
                          ->first();

            if (!$evento) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'El evento no existe o no tienes permisos para modificarlo',
                    'status' => 'error'
                ], 403);
            }

            // Validar datos de entrada
            $validated = $request->validate([
                'titulo' => 'sometimes|string|max:255',
                'descripcion' => 'sometimes|string',
                'fecha' => 'sometimes|date|after:today',
                'hora' => 'sometimes|date_format:H:i',
                'ubicacion' => 'sometimes|string|max:255',
                'categoria' => 'sometimes|string|max:100',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'es_online' => 'sometimes|boolean',
                'enlace_streaming' => 'required_if:es_online,true|nullable|url',
                'tipos_entrada' => 'sometimes|array|min:1',
                'tipos_entrada.*.idTipoEntrada' => 'sometimes|exists:tipo_entrada,idTipoEntrada',
                'tipos_entrada.*.nombre' => 'required_with:tipos_entrada|string|max:100',
                'tipos_entrada.*.precio' => 'required_with:tipos_entrada|numeric|min:0',
                'tipos_entrada.*.cantidad_disponible' => 'required_if:tipos_entrada.*.es_ilimitado,false|nullable|integer|min:1',
                'tipos_entrada.*.descripcion' => 'nullable|string',
                'tipos_entrada.*.es_ilimitado' => 'required_with:tipos_entrada|boolean',
                'tipos_entrada.*.activo' => 'sometimes|boolean'
            ]);

            // Mapear campos directamente
            if (isset($validated['fecha'])) {
                $evento->fechaEvento = $validated['fecha'];
            }
            if (isset($validated['hora'])) {
                $evento->hora = $validated['hora'];
            }
            // Otros campos que necesites mapear
            $evento->save();

            // Procesar la imagen si se proporciona
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('eventos', 'public');
                $validated['imagen'] = $imagenPath;
            }

            // Actualizar el evento
            $evento->update($validated);

            // Actualizar tipos de entrada si se proporcionan
            if (isset($validated['tipos_entrada'])) {
                foreach ($validated['tipos_entrada'] as $tipoEntrada) {
                    if (isset($tipoEntrada['idTipoEntrada'])) {
                        // Actualizar tipo de entrada existente
                        $tipoEntradaModel = $evento->tiposEntrada()
                            ->where('idTipoEntrada', $tipoEntrada['idTipoEntrada'])
                            ->first();

                        if ($tipoEntradaModel) {
                            $tipoEntradaModel->update([
                                'nombre' => $tipoEntrada['nombre'],
                                'precio' => $tipoEntrada['precio'],
                                'cantidad_disponible' => $tipoEntrada['es_ilimitado'] ? null : $tipoEntrada['cantidad_disponible'],
                                'descripcion' => $tipoEntrada['descripcion'] ?? null,
                                'es_ilimitado' => $tipoEntrada['es_ilimitado'],
                                'activo' => $tipoEntrada['activo'] ?? true
                            ]);
                        }
                    } else {
                        // Crear nuevo tipo de entrada
                        $evento->tiposEntrada()->create([
                            'nombre' => $tipoEntrada['nombre'],
                            'precio' => $tipoEntrada['precio'],
                            'cantidad_disponible' => $tipoEntrada['es_ilimitado'] ? null : $tipoEntrada['cantidad_disponible'],
                            'entradas_vendidas' => 0,
                            'descripcion' => $tipoEntrada['descripcion'] ?? null,
                            'es_ilimitado' => $tipoEntrada['es_ilimitado'],
                            'activo' => true
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Evento actualizado con éxito',
                'data' => $evento->load('tiposEntrada'),
                'status' => 'success'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar evento: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al actualizar el evento',
                'message' => 'No se pudo actualizar el evento',
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Obtiene todos los eventos creados por el organizador autenticado
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMisEventos(Request $request)
    {
        try {
            // Verificar si el usuario está autenticado
            if (!$request->user()) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Debes iniciar sesión para ver tus eventos',
                    'status' => 'error'
                ], 401);
            }

            // Verificar si el usuario es un organizador
            if ($request->user()->role !== 'organizador') {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los organizadores pueden acceder a esta funcionalidad',
                    'status' => 'error'
                ], 403);
            }

            // Obtener el idOrganizador usando user_id
            $organizador = Organizador::where('user_id', $request->user()->idUser)->first();
            
            if (!$organizador) {
                Log::error('Organizador no encontrado para el usuario ID: ' . $request->user()->idUser);
                return response()->json([
                    'error' => 'Organizador no encontrado',
                    'message' => 'No se encontró el perfil de organizador asociado a tu cuenta',
                    'status' => 'error'
                ], 404);
            }

            // Obtener todos los eventos del organizador ordenados por fecha
            $eventos = Evento::where('idOrganizador', $organizador->idOrganizador)
                ->with(['entradas']) 
                ->orderBy('fechaEvento', 'asc')
                ->get();
            
            // Transformar para añadir URL completa de las imágenes
            $eventosTransformados = $eventos->map(function($evento) {
                $eventoArray = $evento->toArray();
                $eventoArray['imagen_url'] = url('/storage/' . $evento->imagen);
                return $eventoArray;
            });

            return response()->json([
                'message' => 'Eventos obtenidos correctamente',
                'eventos' => $eventosTransformados,
                'total' => count($eventos),
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener eventos del organizador: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al obtener eventos',
                'message' => 'No se pudieron obtener tus eventos',
                'debug' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Obtener eventos por categoría
     *
     * @param string $categoria La categoría de eventos a buscar
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventosByCategoria($categoria, Request $request)
    {
        try {
            // Normalizar la categoría (primera letra mayúscula, resto minúsculas)
            $categoriaNormalizada = ucfirst(strtolower($categoria));
            
            // Verificar si la categoría es válida (opcional, dependiendo de tus requisitos)
            $categoriasValidas = \App\Enums\CategoriaEvento::getAllValues();
            
            // Si quieres validar la categoría, descomenta estas líneas
            /*
            if (!in_array($categoriaNormalizada, $categoriasValidas)) {
                return response()->json([
                    'error' => 'Categoría no válida',
                    'message' => 'La categoría proporcionada no es válida',
                    'categorias_validas' => $categoriasValidas,
                    'status' => 'error'
                ], 400);
            }
            */
            
            // Obtener los eventos de la categoría especificada
            $eventos = Evento::with([
                'organizador', 
                'organizador.user',
                'tiposEntrada'
            ])
            ->where('categoria', $categoriaNormalizada)
            ->orderBy('fechaEvento', 'asc')
            ->get();
            
            // Verificar si hay usuario autenticado para comprobar favoritos
            $participante = null;
            $user = $request->user();
            if ($user && $user->role === 'participante') {
                $participante = Participante::where('idUser', $user->idUser)->first();
            }
            
            // Transformar los datos para la respuesta
            $eventosData = $eventos->map(function ($evento) use ($participante) {
                // Verificar si es favorito
                $isFavorito = false;
                if ($participante) {
                    $isFavorito = DB::table('favorito')
                        ->where('idParticipante', $participante->idParticipante)
                        ->where('idEvento', $evento->idEvento)
                        ->exists();
                }
                
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
                    'is_favorite' => $isFavorito,
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
                'categoria' => $categoriaNormalizada,
                'eventos' => $eventosData,
                'total' => count($eventosData),
                'message' => 'Eventos obtenidos correctamente',
                'status' => 'success'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los eventos por categoría',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Obtener el precio mínimo de cada evento
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrecioMinimoEventos()
    {
        try {
            // Obtener todos los eventos con sus tipos de entrada
            $eventos = Evento::with(['tiposEntrada' => function($query) {
                $query->where('activo', true)
                      ->orderBy('precio', 'asc');
            }])->get();

            // Transformar los datos para mostrar solo el precio mínimo
            $eventosData = $eventos->map(function ($evento) {
                $precioMinimo = null;
                if ($evento->tiposEntrada->isNotEmpty()) {
                    $precioMinimo = $evento->tiposEntrada->first()->precio;
                }

                return [
                    'id' => $evento->idEvento,
                    'nombreEvento' => $evento->nombreEvento,
                    'precio_minimo' => $precioMinimo,
                    'moneda' => 'EUR'
                ];
            });

            return response()->json([
                'message' => 'Precios mínimos obtenidos con éxito',
                'eventos' => $eventosData
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener precios mínimos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los precios mínimos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el precio mínimo de un evento específico
     *
     * @param int $id ID del evento
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrecioMinimoEvento($id)
    {
        try {
            // Buscar el evento con sus tipos de entrada
            $evento = Evento::with(['tiposEntrada' => function($query) {
                $query->where('activo', true)
                      ->orderBy('precio', 'asc');
            }])->find($id);

            // Si no se encuentra el evento
            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            // Obtener el precio mínimo
            $precioMinimo = null;
            if ($evento->tiposEntrada->isNotEmpty()) {
                $precioMinimo = $evento->tiposEntrada->first()->precio;
            }

            return response()->json([
                'message' => 'Precio mínimo obtenido con éxito',
                'evento' => [
                    'id' => $evento->idEvento,
                    'nombreEvento' => $evento->nombreEvento,
                    'precio_minimo' => $precioMinimo,
                    'moneda' => 'EUR'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener precio mínimo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el precio mínimo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el precio máximo de cada evento
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrecioMaximoEventos()
    {
        try {
            // Obtener todos los eventos con sus tipos de entrada
            $eventos = Evento::with(['tiposEntrada' => function($query) {
                $query->where('activo', true)
                      ->orderBy('precio', 'desc');
            }])->get();

            // Transformar los datos para mostrar solo el precio máximo
            $eventosData = $eventos->map(function ($evento) {
                $precioMaximo = null;
                if ($evento->tiposEntrada->isNotEmpty()) {
                    $precioMaximo = $evento->tiposEntrada->first()->precio;
                }

                return [
                    'id' => $evento->idEvento,
                    'nombreEvento' => $evento->nombreEvento,
                    'precio_maximo' => $precioMaximo,
                    'moneda' => 'EUR'
                ];
            });

            return response()->json([
                'message' => 'Precios máximos obtenidos con éxito',
                'eventos' => $eventosData
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener precios máximos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los precios máximos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el precio máximo de un evento específico
     *
     * @param int $id ID del evento
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrecioMaximoEvento($id)
    {
        try {
            // Buscar el evento con sus tipos de entrada
            $evento = Evento::with(['tiposEntrada' => function($query) {
                $query->where('activo', true)
                      ->orderBy('precio', 'desc');
            }])->find($id);

            // Si no se encuentra el evento
            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            // Obtener el precio máximo
            $precioMaximo = null;
            if ($evento->tiposEntrada->isNotEmpty()) {
                $precioMaximo = $evento->tiposEntrada->first()->precio;
            }

            return response()->json([
                'message' => 'Precio máximo obtenido con éxito',
                'evento' => [
                    'id' => $evento->idEvento,
                    'nombreEvento' => $evento->nombreEvento,
                    'precio_maximo' => $precioMaximo,
                    'moneda' => 'EUR'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener precio máximo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el precio máximo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 