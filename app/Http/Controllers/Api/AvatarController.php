<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\AvatarHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AvatarController extends Controller
{
    /**
     * Sube un avatar para el usuario autenticado
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $user = auth()->user();
            $avatar = $request->file('avatar');
            
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::delete('public/avatars/' . basename($user->avatar));
            }

            // Generar nombre único para el archivo
            $filename = Str::random(40) . '.' . $avatar->getClientOriginalExtension();
            
            // Guardar el archivo
            $path = $avatar->storeAs('public/avatars', $filename);
            
            // Actualizar el avatar del usuario con la URL correcta
            $user->avatar = '/storage/avatars/' . $filename;
            $user->save();

            return response()->json([
                'message' => 'Avatar actualizado correctamente',
                'avatar_url' => $user->avatar
            ]);
        } catch (\Exception $e) {
            Log::error('Error al subir avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al subir el avatar'
            ], 500);
        }
    }

    /**
     * Genera un avatar basado en las iniciales del usuario
     */
    public function generate()
    {
        try {
            $user = auth()->user();
            
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::delete('public/avatars/' . basename($user->avatar));
            }

            // Generar nuevo avatar
            $avatarHelper = new AvatarHelper();
            $avatar = $avatarHelper->generateAvatar($user->name, $user->role);
            
            // Generar nombre único para el archivo
            $filename = Str::random(40) . '.png';
            
            // Guardar el archivo
            Storage::put('public/avatars/' . $filename, $avatar);
            
            // Actualizar el avatar del usuario con la URL correcta
            $user->avatar = '/storage/avatars/' . $filename;
            $user->save();

            return response()->json([
                'message' => 'Avatar generado correctamente',
                'avatar_url' => $user->avatar
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al generar el avatar'
            ], 500);
        }
    }

    /**
     * Elimina el avatar del usuario
     */
    public function delete()
    {
        try {
            $user = auth()->user();
            
            if ($user->avatar) {
                Storage::delete('public/avatars/' . basename($user->avatar));
                $user->avatar = null;
                $user->save();
            }

            return response()->json([
                'message' => 'Avatar eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el avatar'
            ], 500);
        }
    }
} 