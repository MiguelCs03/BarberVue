<?php

use App\Http\Controllers\CitaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::prefix('citas')->name('api.citas.')->group(function () {
    // Listar/consultar (GET)
    Route::post('/barberos-disponibles', [CitaController::class, 'getBarberosDisponibles'])
        ->name('barberos-disponibles');    ///api.citas.barberos-disponibles, para acceder desde vue
    
})->middleware('auth:sanctum');
