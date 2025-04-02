<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\GoogleAuthController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/eventos', [EventoController::class, 'getAllEventos']);
Route::get('/eventos/{id}', [EventoController::class, 'getEventoById']);

// Rutas protegidas que requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Información del usuario 
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Perfil del usuario
    Route::get('/profile', [AuthController::class, 'getProfile']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rutas de favoritos
    Route::get('/favoritos', [FavoritoController::class, 'getFavoritos']);
    Route::post('/favoritos', [FavoritoController::class, 'addFavorito']);
    Route::delete('/favoritos/{idEvento}', [FavoritoController::class, 'removeFavorito']);
    Route::get('/favoritos/check/{idEvento}', [FavoritoController::class, 'checkFavorito']);

    // Rutas de eventos (protegidas)
    Route::post('/eventos', [EventoController::class, 'createEvento']);
    Route::delete('/eventos/{id}', [EventoController::class, 'deleteEvento']);
    Route::put('/eventos/{id}', [EventoController::class, 'updateEvento']);
    Route::get('/mis-eventos', [EventoController::class, 'getMisEventos']);
});

// Rutas para autenticación con Google
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);