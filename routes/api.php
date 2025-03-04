<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\FavoritoController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/favoritos', [FavoritoController::class, 'getFavoritos']);
    Route::post('/favoritos', [FavoritoController::class, 'addFavorito']);
    Route::delete('/favoritos/{idEvento}', [FavoritoController::class, 'removeFavorito']);
    Route::get('/favoritos/check/{idEvento}', [FavoritoController::class, 'checkFavorito']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/eventos', [EventoController::class, 'getAllEventos']);
Route::get('/eventos/{id}', [EventoController::class, 'getEventoById']);