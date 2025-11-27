<?php

use App\Http\Controllers\CitaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhook de PagoFácil (sin autenticación, sin CSRF)
Route::post('/citas/callback-pagofacil', [CitaController::class, 'handleCallback'])
    ->name('api.citas.callback-pagofacil');

Route::prefix('citas')->name('api.citas.')->group(function () {
    // Verificar estado de pago (requiere autenticación)
    Route::get('/{id}/verificar-pago', [CitaController::class, 'verificarEstadoPago'])
        ->name('verificar-pago');
})->middleware('auth:sanctum');
