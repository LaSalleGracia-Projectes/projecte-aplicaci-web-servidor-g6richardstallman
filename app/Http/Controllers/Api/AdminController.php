<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Evento;
use App\Models\TipoEntrada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getAllUsers()
    {
        try {
            $users = User::with(['organizador', 'participante'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {
                    return [
                        'idUser' => $user->idUser,
                        'nombre' => $user->nombre,
                        'apellido1' => $user->apellido1,
                        'apellido2' => $user->apellido2,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                        'organizador' => $user->organizador ? [
                            'idOrganizador' => $user->organizador->idOrganizador,
                            'nombre_organizacion' => $user->organizador->nombre_organizacion,
                            'telefono_contacto' => $user->organizador->telefono_contacto
                        ] : null,
                        'participante' => $user->participante ? [
                            'idParticipante' => $user->participante->idParticipante,
                            'dni' => $user->participante->dni,
                            'telefono' => $user->participante->telefono
                        ] : null
                    ];
                });

            return response()->json([
                'message' => 'Usuarios obtenidos exitosamente',
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changeUserPassword(Request $request, $userId)
    {
        try {
            // Validar la nueva contraseña
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ], [
                'new_password.required' => 'La nueva contraseña es obligatoria',
                'new_password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'new_password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar el usuario
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Cambiar la contraseña
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada exitosamente',
                'user' => [
                    'idUser' => $user->idUser,
                    'email' => $user->email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cambiar la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateUser(Request $request, $userId)
    {
        try {
            // Buscar el usuario
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'apellido1' => 'sometimes|required|string|max:255',
                'apellido2' => 'nullable|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $userId . ',idUser',
                'role' => 'sometimes|required|in:admin,organizador,participante',
                'password' => 'sometimes|required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.string' => 'El nombre debe ser texto',
                'nombre.max' => 'El nombre no puede tener más de 255 caracteres',
                'apellido1.required' => 'El primer apellido es obligatorio',
                'apellido1.string' => 'El primer apellido debe ser texto',
                'apellido1.max' => 'El primer apellido no puede tener más de 255 caracteres',
                'apellido2.string' => 'El segundo apellido debe ser texto',
                'apellido2.max' => 'El segundo apellido no puede tener más de 255 caracteres',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'email.unique' => 'Este email ya está registrado',
                'role.required' => 'El rol es obligatorio',
                'role.in' => 'El rol debe ser admin, organizador o participante',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar solo los campos proporcionados
            $updateData = $request->only(['nombre', 'apellido1', 'apellido2', 'email', 'role']);
            
            // Si se proporciona una nueva contraseña, hashearla
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user' => [
                    'idUser' => $user->idUser,
                    'nombre' => $user->nombre,
                    'apellido1' => $user->apellido1,
                    'apellido2' => $user->apellido2,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEvento(Request $request, $idEvento)
    {
        try {
            // Buscar el evento
            $evento = Evento::find($idEvento);
            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'titulo' => 'sometimes|required|string|max:255',
                'descripcion' => 'sometimes|required|string',
                'fecha' => 'sometimes|required|date|after:today',
                'hora' => 'sometimes|required|date_format:H:i',
                'ubicacion' => 'sometimes|required|string|max:255',
                'categoria' => 'sometimes|required|string|max:100',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'es_online' => 'sometimes|required|boolean',
                'enlace_streaming' => 'required_if:es_online,true|nullable|url',
                'tipos_entrada' => 'sometimes|array|min:1',
                'tipos_entrada.*.idTipoEntrada' => 'sometimes|exists:tipo_entrada,idTipoEntrada',
                'tipos_entrada.*.nombre' => 'required_with:tipos_entrada|string|max:100',
                'tipos_entrada.*.precio' => 'required_with:tipos_entrada|numeric|min:0',
                'tipos_entrada.*.cantidad_disponible' => 'required_if:tipos_entrada.*.es_ilimitado,false|nullable|integer|min:1',
                'tipos_entrada.*.descripcion' => 'nullable|string',
                'tipos_entrada.*.es_ilimitado' => 'required_with:tipos_entrada|boolean',
                'tipos_entrada.*.activo' => 'sometimes|boolean'
            ], [
                'titulo.required' => 'El título es obligatorio',
                'titulo.string' => 'El título debe ser texto',
                'titulo.max' => 'El título no puede tener más de 255 caracteres',
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
                'enlace_streaming.required_if' => 'El enlace de streaming es obligatorio para eventos online',
                'enlace_streaming.url' => 'El enlace de streaming debe ser una URL válida',
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

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Procesar la imagen si se proporciona
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('eventos', 'public');
                $evento->imagen = $imagenPath;
            }

            // Actualizar los campos del evento
            $updateData = $request->only([
                'titulo', 'descripcion', 'fecha', 'hora', 
                'ubicacion', 'categoria', 'es_online', 'enlace_streaming'
            ]);

            // Mapear campos si es necesario
            if (isset($updateData['titulo'])) {
                $evento->nombreEvento = $updateData['titulo'];
            }
            if (isset($updateData['fecha'])) {
                $evento->fechaEvento = $updateData['fecha'];
            }
            if (isset($updateData['ubicacion'])) {
                $evento->lugar = $updateData['ubicacion'];
            }

            $evento->save();

            // Actualizar tipos de entrada si se proporcionan
            if (isset($request->tipos_entrada)) {
                foreach ($request->tipos_entrada as $tipoEntrada) {
                    if (isset($tipoEntrada['idTipoEntrada'])) {
                        // Actualizar tipo de entrada existente
                        $tipoEntradaModel = TipoEntrada::where('idTipoEntrada', $tipoEntrada['idTipoEntrada'])
                            ->where('idEvento', $evento->idEvento)
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
                        TipoEntrada::create([
                            'idEvento' => $evento->idEvento,
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
                'message' => 'Evento actualizado exitosamente',
                'evento' => $evento->load('tiposEntrada')
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar evento: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteEvento($idEvento)
    {
        try {
            // Buscar el evento
            $evento = Evento::find($idEvento);
            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            // Verificar si hay entradas vendidas
            $tieneEntradasVendidas = false;
            foreach ($evento->tiposEntrada as $tipoEntrada) {
                if ($tipoEntrada->entradas_vendidas > 0) {
                    $tieneEntradasVendidas = true;
                    break;
                }
            }

            if ($tieneEntradasVendidas) {
                return response()->json([
                    'message' => 'No se puede eliminar el evento porque tiene entradas vendidas'
                ], 422);
            }

            // Eliminar la imagen si existe
            if ($evento->imagen) {
                Storage::disk('public')->delete($evento->imagen);
            }

            // Eliminar los tipos de entrada asociados
            $evento->tiposEntrada()->delete();

            // Eliminar el evento
            $evento->delete();

            return response()->json([
                'message' => 'Evento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar evento: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllEventos()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        try {
            $eventos = Evento::with(['tiposEntrada', 'organizador'])
                ->orderBy('fechaEvento', 'desc')
                ->get()
                ->map(function ($evento) {
                    return [
                        'idEvento' => $evento->idEvento,
                        'nombreEvento' => $evento->nombreEvento,
                        'descripcion' => $evento->descripcion,
                        'fechaEvento' => $evento->fechaEvento,
                        'hora' => $evento->hora,
                        'ubicacion' => $evento->lugar,
                        'categoria' => $evento->categoria,
                        'imagen' => $evento->imagen ? Storage::url($evento->imagen) : null,
                        'es_online' => $evento->es_online,
                        'enlace_streaming' => $evento->enlace_streaming,
                        'organizador' => $evento->organizador ? [
                            'idUser' => $evento->organizador->idUser,
                            'nombre' => $evento->organizador->nombre,
                            'apellido1' => $evento->organizador->apellido1,
                            'apellido2' => $evento->organizador->apellido2,
                            'email' => $evento->organizador->email
                        ] : null,
                        'tiposEntrada' => $evento->tiposEntrada->map(function ($tipoEntrada) {
                            return [
                                'idTipoEntrada' => $tipoEntrada->idTipoEntrada,
                                'nombre' => $tipoEntrada->nombre,
                                'precio' => $tipoEntrada->precio,
                                'cantidad_disponible' => $tipoEntrada->cantidad_disponible,
                                'entradas_vendidas' => $tipoEntrada->entradas_vendidas,
                                'descripcion' => $tipoEntrada->descripcion,
                                'es_ilimitado' => $tipoEntrada->es_ilimitado,
                                'activo' => $tipoEntrada->activo
                            ];
                        }),
                        'created_at' => $evento->created_at,
                        'updated_at' => $evento->updated_at
                    ];
                });

            return response()->json([
                'message' => 'Eventos obtenidos exitosamente',
                'eventos' => $eventos
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener eventos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los eventos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 