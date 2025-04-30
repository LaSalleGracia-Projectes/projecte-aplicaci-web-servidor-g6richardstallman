<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\TipoEntradaController;
use App\Http\Controllers\Api\VentaEntradaController;
use App\Http\Controllers\Api\OrganizadorFavoritoController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\OrganizadorController;
use App\Http\Controllers\Api\AvatarController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/eventos', [EventoController::class, 'getAllEventos']);
Route::get('/eventos/precios-minimos', [EventoController::class, 'getPrecioMinimoEventos']);
Route::get('/eventos/{id}/precio-minimo', [EventoController::class, 'getPrecioMinimoEvento']);
Route::get('/eventos/precios-maximos', [EventoController::class, 'getPrecioMaximoEventos']);
Route::get('/eventos/{id}/precio-maximo', [EventoController::class, 'getPrecioMaximoEvento']);
Route::get('/eventos/categoria/{categoria}', [EventoController::class, 'getEventosByCategoria']);
Route::get('/eventos/{id}', [EventoController::class, 'getEventoById']);
Route::get('/organizadores', [OrganizadorController::class, 'getAllOrganizadores']);
Route::get('/organizadores/{id}', [OrganizadorController::class, 'getOrganizadorById']);
Route::get('/organizadores/{id}/eventos', [OrganizadorController::class, 'getEventosByOrganizador']);
Route::get('/organizadores/{id}/es-favorito', [OrganizadorController::class, 'checkIsFavorito']);

// Rutas protegidas que requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Información del usuario 
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Perfil del usuario
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
    // Cambiar contraseña
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
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

    // Rutas para tipos de entrada (protegidas por auth:sanctum y middleware de organizador)
    Route::post('/eventos/{idEvento}/tipos-entrada', [TipoEntradaController::class, 'store']);
    Route::put('/eventos/{idEvento}/tipos-entrada/{idTipoEntrada}', [TipoEntradaController::class, 'update']);
    Route::delete('/eventos/{idEvento}/tipos-entrada/{idTipoEntrada}', [TipoEntradaController::class, 'destroy']);

    // Rutas para venta de entradas
    Route::post('/compras', [VentaEntradaController::class, 'comprar']);
    Route::get('/compras', [VentaEntradaController::class, 'listarCompras']);
    
    // Nueva ruta para ver detalle de una compra específica
    Route::get('/compras/{id}', [VentaEntradaController::class, 'detalleCompra']);
    
    // Nueva ruta para generar factura
    Route::get('/compras/{id}/factura', [VentaEntradaController::class, 'generarFactura']);

    // Nueva ruta para eliminar cuenta
    Route::delete('/account', [AuthController::class, 'deleteAccount']);

    // Rutas para organizadores favoritos
    Route::get('/organizadores-favoritos', [OrganizadorFavoritoController::class, 'getOrganizadoresFavoritos']);
    Route::post('/organizadores-favoritos', [OrganizadorFavoritoController::class, 'addOrganizadorFavorito']);
    Route::delete('/organizadores-favoritos/{idOrganizador}', [OrganizadorFavoritoController::class, 'removeOrganizadorFavorito']);
    Route::get('/organizadores-favoritos/check/{idOrganizador}', [OrganizadorFavoritoController::class, 'checkOrganizadorFavorito']);

    // Rutas para generar PDFs
    Route::get('/factura/{id}/pdf', [PdfController::class, 'generarFacturaPdf']);
    Route::get('/entrada/{id}/pdf', [PdfController::class, 'generarEntradaPdf']);

    // Rutas de avatares
    Route::post('/avatar/upload', [AvatarController::class, 'upload']);
    Route::post('/avatar/generate', [AvatarController::class, 'generate']);
    Route::delete('/avatar', [AvatarController::class, 'delete']);
});

// Ruta pública para obtener tipos de entrada de un evento
Route::get('/eventos/{idEvento}/tipos-entrada', [TipoEntradaController::class, 'index']);

// Rutas para autenticación con Google
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::post('/auth/google/verify-token', [AuthController::class, 'verifyGoogleToken']);
