<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;
use App\Mail\RegistroConfirmacion;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActualizacionPassword;
use Illuminate\Support\Facades\DB;
use App\Models\VentaEntrada;
use App\Models\Factura;
use App\Models\Entrada;
use App\Models\Favorito;
use App\Models\Valoracion;
use App\Models\Evento;
use App\Models\TipoEntrada;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Verificar si hay un token activo en el header
        if ($request->bearerToken()) {
            return response()->json([
                'error' => 'Sesión activa',
                'message' => 'Ya existe una sesión activa. Cierre sesión antes de registrar un nuevo usuario.',
                'status' => 'error'
            ], 403);
        }

        try {
            // Validar datos de entrada
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido1' => 'required|string|max:255',
                'apellido2' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|in:organizador,participante',
                'nombre_organizacion' => 'required_if:role,organizador|string|max:255',
                'telefono_contacto' => 'required_if:role,organizador|string|max:15',
                'dni' => 'required_if:role,participante|string|max:10|unique:participante,dni',
                'telefono' => 'required_if:role,participante|string|max:15',
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'apellido1.required' => 'El primer apellido es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'email.unique' => 'Este email ya está registrado',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                'role.required' => 'El rol es obligatorio',
                'role.in' => 'El rol debe ser organizador o participante',
                'dni.required_if' => 'El DNI es obligatorio para participantes',
                'dni.unique' => 'Este DNI ya está registrado',
                'telefono.required_if' => 'El teléfono es obligatorio para participantes',
                'nombre_organizacion.required_if' => 'El nombre de la organización es obligatorio para organizadores',
                'telefono_contacto.required_if' => 'El teléfono de contacto es obligatorio para organizadores'
            ]);

            $user = User::create([
                'nombre' => $validated['nombre'],
                'apellido1' => $validated['apellido1'],
                'apellido2' => $validated['apellido2'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role']
            ]);

            // Enviar correo de confirmación
            Mail::to($user->email)->send(new RegistroConfirmacion($user));

            // Eliminar tokens existentes (por si acaso)
            $user->tokens()->delete();
            
            // Crear un token con nombre fijo para que siempre sea el mismo
            $token = $user->createToken('persistent_token')->plainTextToken;
            $user->remember_token = $token;
            $user->save();

            if ($validated['role'] === 'organizador') {
                Organizador::create([
                    'nombre_organizacion' => $validated['nombre_organizacion'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'user_id' => $user->idUser
                ]);
            } elseif ($validated['role'] === 'participante') {
                Participante::create([
                    'dni' => $validated['dni'],
                    'telefono' => $validated['telefono'],
                    'idUser' => $user->idUser
                ]);
            }

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en el registro: '.$e->getMessage());
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'password.required' => 'La contraseña es obligatoria'
            ]);

            // Buscar usuario por email
            $user = User::where('email', $validated['email'])->first();
            
            // Si no existe el usuario o la contraseña es incorrecta
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'error' => 'Credenciales incorrectas',
                    'message' => 'El email o la contraseña son incorrectos',
                    'status' => 'error'
                ], 401);
            }

            // Eliminar cualquier token existente para permitir nuevo login
            $user->tokens()->delete();
            
            // Crear token nuevo
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Guardar el token para referencia futura
            $user->remember_token = $token;
            $user->save();

            return response()->json([
                'message' => 'Login exitoso',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'status' => 'success'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en el login: '.$e->getMessage());
            
            return response()->json([
                'error' => 'Error en el login',
                'message' => 'No se pudo iniciar sesión. Por favor, inténtelo de nuevo.',
                'status' => 'error'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Verificar si hay un token de acceso
        if (!$request->bearerToken()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No hay sesión activa para cerrar',
                'status' => 'error'
            ], 401);
        }
        
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            
            // Si el usuario es válido, cerrar su sesión
            if ($user) {
                $user->tokens()->delete();
                return response()->json([
                    'message' => 'Sesión cerrada correctamente',
                    'status' => 'success'
                ], 200);
            }
            
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Token no válido',
                'status' => 'error'
            ], 401);
            
        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al cerrar sesión',
                'message' => 'No se pudo cerrar la sesión correctamente',
                'status' => 'error'
            ], 500);
        }
    }
    
    public function resetPassword(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'email' => 'required|email',
                'identificador' => 'required|string' // Puede ser DNI o teléfono de contacto
            ]);

            // Buscar el usuario por email
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'No se encontró ningún usuario con ese correo electrónico'
                ], 404);
            }

            $identificadorValido = false;

            // Verificar según el rol del usuario
            if ($user->role === 'participante') {
                // Para participantes, verificamos el DNI
                $participante = Participante::where('idUser', $user->idUser)
                                          ->where('dni', $validated['identificador'])
                                          ->first();
                
                $identificadorValido = ($participante !== null);
            } 
            elseif ($user->role === 'organizador') {
                // Para organizadores, verificamos el teléfono de contacto
                $organizador = Organizador::where('user_id', $user->idUser)
                                        ->where('telefono_contacto', $validated['identificador'])
                                        ->first();
                
                $identificadorValido = ($organizador !== null);
            }

            if (!$identificadorValido) {
                return response()->json([
                    'message' => 'El identificador proporcionado no coincide con el usuario'
                ], 400);
            }

            // Generar una nueva contraseña aleatoria
            $newPassword = Str::random(10);
            
            // Actualizar la contraseña del usuario
            $user->password = Hash::make($newPassword);
            $user->save();

            // Enviar correo electrónico con la nueva contraseña
            Mail::to($user->email)->send(new ActualizacionPassword($user, $newPassword));

            // Devolver la nueva contraseña
            return response()->json([
                'message' => 'Contraseña restablecida con éxito. Se ha enviado un correo electrónico con los detalles.',
                'password' => $newPassword
            ]);

        } catch (\Exception $e) {
            Log::error('Error en resetPassword: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al restablecer la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }

            $profileData = [
                'id' => $user->idUser,
                'nombre' => $user->nombre,
                'apellido1' => $user->apellido1,
                'apellido2' => $user->apellido2,
                'email' => $user->email,
                'role' => $user->role,
                'tipo_usuario' => $user->role === 'participante' ? 'Participante' : 'Organizador'
            ];

            // Añadir datos específicos según el rol
            if ($user->role === 'participante') {
                $participante = Participante::where('idUser', $user->idUser)->first();
                if ($participante) {
                    $profileData['dni'] = $participante->dni;
                    $profileData['telefono'] = $participante->telefono;
                }
            } elseif ($user->role === 'organizador') {
                $organizador = Organizador::where('user_id', $user->idUser)->first();
                if ($organizador) {
                    $profileData['nombre_organizacion'] = $organizador->nombre_organizacion;
                    $profileData['telefono_contacto'] = $organizador->telefono_contacto;
                }
            }

            return response()->json([
                'message' => 'Perfil recuperado con éxito',
                'data' => $profileData,
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener perfil: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener el perfil',
                'message' => 'No se pudo recuperar la información del perfil',
                'status' => 'error'
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }

            // Validar los datos de entrada
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|string|same:new_password'
            ], [
                'current_password.required' => 'La contraseña actual es obligatoria',
                'new_password.required' => 'La nueva contraseña es obligatoria',
                'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
                'new_password.different' => 'La nueva contraseña debe ser diferente a la actual',
                'confirm_password.required' => 'La confirmación de contraseña es obligatoria',
                'confirm_password.same' => 'Las contraseñas no coinciden'
            ]);

            // Verificar que la contraseña actual es correcta
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'error' => 'Contraseña incorrecta',
                    'message' => 'La contraseña actual no es correcta',
                    'status' => 'error'
                ], 400);
            }

            // Verificar que la nueva contraseña es diferente a la actual
            if ($validated['current_password'] === $validated['new_password']) {
                return response()->json([
                    'error' => 'Contraseña inválida',
                    'message' => 'La nueva contraseña debe ser diferente a la actual',
                    'status' => 'error'
                ], 400);
            }

            // Actualizar la contraseña
            $user->password = Hash::make($validated['new_password']);
            $user->save();

            // Opcional: revocar todos los tokens para forzar un nuevo login
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Contraseña actualizada con éxito',
                'status' => 'success'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al cambiar contraseña: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cambiar la contraseña',
                'message' => 'No se pudo actualizar la contraseña',
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Eliminar la cuenta del usuario autenticado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }

            // Validar la contraseña para confirmar la eliminación
            $validated = $request->validate([
                'password' => 'required|string',
                'confirm_deletion' => 'required|boolean|accepted'
            ], [
                'password.required' => 'La contraseña es obligatoria para confirmar la eliminación',
                'confirm_deletion.required' => 'Debe confirmar que desea eliminar la cuenta',
                'confirm_deletion.accepted' => 'Debe confirmar que desea eliminar la cuenta'
            ]);

            // Verificar la contraseña
            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'error' => 'Contraseña incorrecta',
                    'message' => 'La contraseña proporcionada no es correcta',
                    'status' => 'error'
                ], 400);
            }

            // Iniciar transacción para garantizar que todo se elimine correctamente
            DB::beginTransaction();
            
            try {
                // Eliminar datos según el rol del usuario
                if ($user->role === 'participante') {
                    $this->deleteParticipante($user);
                } else if ($user->role === 'organizador') {
                    $this->deleteOrganizador($user);
                }
                
                // Eliminar tokens de acceso
                $user->tokens()->delete();
                
                // Eliminar el usuario
                $user->delete();
                
                DB::commit();
                
                return response()->json([
                    'message' => 'Cuenta eliminada correctamente',
                    'status' => 'success'
                ], 200);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al eliminar cuenta: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al eliminar la cuenta',
                'message' => 'No se pudo eliminar la cuenta correctamente: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Elimina los datos relacionados con un participante
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    private function deleteParticipante(User $user)
    {
        // Buscar el participante asociado
        $participante = Participante::where('idUser', $user->idUser)->first();
        
        if ($participante) {
            // Verificar si hay compras asociadas
            $compras = VentaEntrada::where('idParticipante', $participante->idParticipante)->get();
            
            // Para cada compra, eliminar la factura asociada si existe
            foreach ($compras as $compra) {
                Factura::where('idEntrada', $compra->idEntrada)
                      ->where('idParticipante', $participante->idParticipante)
                      ->delete();
                
                // Opcional: marcar la entrada como disponible nuevamente
                // Solo si el evento no ha pasado
                $entrada = Entrada::find($compra->idEntrada);
                if ($entrada && $entrada->evento->fechaEvento > now()) {
                    // Aquí la lógica para liberar la entrada
                }
                
                // Eliminar la compra
                $compra->delete();
            }
            
            // Eliminar favoritos si existen
            Favorito::where('idParticipante', $participante->idParticipante)->delete();
            
            // Eliminar valoraciones si existen
            Valoracion::where('idParticipante', $participante->idParticipante)->delete();
            
            // Finalmente eliminar el participante
            $participante->delete();
        }
    }

    /**
     * Elimina los datos relacionados con un organizador
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    private function deleteOrganizador(User $user)
    {
        // Buscar el organizador asociado
        $organizador = Organizador::where('user_id', $user->idUser)->first();
        
        if ($organizador) {
            // Verificar si hay eventos asociados
            $eventos = Evento::where('idOrganizador', $organizador->idOrganizador)->get();
            
            // Para cada evento, verificar si se puede eliminar
            foreach ($eventos as $evento) {
                // Si el evento ya pasó o no tiene entradas vendidas, eliminar
                if ($evento->fechaEvento < now() || $evento->entradas_vendidas == 0) {
                    // Eliminar tipos de entrada asociados
                    TipoEntrada::where('idEvento', $evento->idEvento)->delete();
                    
                    // Eliminar entradas asociadas
                    $entradas = Entrada::where('idEvento', $evento->idEvento)->get();
                    foreach ($entradas as $entrada) {
                        // Eliminar facturas asociadas a la entrada
                        Factura::where('idEntrada', $entrada->idEntrada)->delete();
                        
                        // Eliminar ventas asociadas a la entrada
                        VentaEntrada::where('idEntrada', $entrada->idEntrada)->delete();
                        
                        // Eliminar la entrada
                        $entrada->delete();
                    }
                    
                    // Eliminar valoraciones del evento
                    Valoracion::where('idEvento', $evento->idEvento)->delete();
                    
                    // Eliminar favoritos del evento
                    Favorito::where('idEvento', $evento->idEvento)->delete();
                    
                    // Eliminar el evento
                    $evento->delete();
                } else {
                    // Si hay entradas vendidas para eventos futuros, lanzar error
                    throw new \Exception('No se puede eliminar la cuenta porque hay eventos con entradas vendidas');
                }
            }
            
            // Finalmente eliminar el organizador
            $organizador->delete();
        }
    }

    /**
     * Actualizar el perfil del usuario autenticado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Usuario no autenticado',
                    'status' => 'error'
                ], 401);
            }

            // Validar los datos básicos del usuario
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'apellido1' => 'sometimes|string|max:255',
                'apellido2' => 'sometimes|nullable|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->idUser . ',idUser',
            ]);

            // Actualizar datos básicos del usuario
            if (isset($validated['nombre'])) {
                $user->nombre = $validated['nombre'];
            }
            if (isset($validated['apellido1'])) {
                $user->apellido1 = $validated['apellido1'];
            }
            if (isset($validated['apellido2'])) {
                $user->apellido2 = $validated['apellido2'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            $user->save();

            // Actualizar datos específicos según el rol
            if ($user->role === 'participante') {
                $participante = Participante::where('idUser', $user->idUser)->first();
                if ($participante) {
                    if ($request->has('dni')) {
                        $participante->dni = $request->input('dni');
                    }
                    if ($request->has('telefono')) {
                        $participante->telefono = $request->input('telefono');
                    }
                    if ($request->has('direccion')) {
                        $participante->direccion = $request->input('direccion');
                    }
                    $participante->save();
                }
            } elseif ($user->role === 'organizador') {
                $organizador = Organizador::where('user_id', $user->idUser)->first();
                if ($organizador) {
                    if ($request->has('nombre_organizacion')) {
                        $organizador->nombre_organizacion = $request->input('nombre_organizacion');
                    }
                    if ($request->has('telefono_contacto')) {
                        $organizador->telefono_contacto = $request->input('telefono_contacto');
                    }
                    if ($request->has('direccion_fiscal')) {
                        $organizador->direccion_fiscal = $request->input('direccion_fiscal');
                    }
                    if ($request->has('cif')) {
                        $organizador->cif = $request->input('cif');
                    }
                    $organizador->save();
                }
            }

            // Obtener perfil actualizado
            $profileData = [
                'id' => $user->idUser,
                'nombre' => $user->nombre,
                'apellido1' => $user->apellido1,
                'apellido2' => $user->apellido2,
                'email' => $user->email,
                'role' => $user->role
            ];

            // Añadir datos específicos según el rol
            if ($user->role === 'participante') {
                $participante = Participante::where('idUser', $user->idUser)->first();
                if ($participante) {
                    $profileData['dni'] = $participante->dni;
                    $profileData['telefono'] = $participante->telefono;
                    if (isset($participante->direccion)) {
                        $profileData['direccion'] = $participante->direccion;
                    }
                }
            } elseif ($user->role === 'organizador') {
                $organizador = Organizador::where('user_id', $user->idUser)->first();
                if ($organizador) {
                    $profileData['nombre_organizacion'] = $organizador->nombre_organizacion;
                    $profileData['telefono_contacto'] = $organizador->telefono_contacto;
                    if (isset($organizador->direccion_fiscal)) {
                        $profileData['direccion_fiscal'] = $organizador->direccion_fiscal;
                    }
                    if (isset($organizador->cif)) {
                        $profileData['cif'] = $organizador->cif;
                    }
                }
            }

            return response()->json([
                'message' => 'Perfil actualizado con éxito',
                'data' => $profileData,
                'status' => 'success'
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar perfil: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al actualizar el perfil',
                'message' => 'No se pudo actualizar la información del perfil',
                'status' => 'error'
            ], 500);
        }
    }
}