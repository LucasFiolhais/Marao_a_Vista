<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeteoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ComentarioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/public/meteo', [MeteoController::class, 'index']);
Route::apiResource('reservas', ReservaController::class);
Route::apiResource('comentarios', ComentarioController::class);