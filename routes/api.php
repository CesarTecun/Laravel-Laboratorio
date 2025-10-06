<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aquí registramos las rutas de la API. Estas rutas son cargadas por el
| RouteServiceProvider y todas serán asignadas al grupo middleware "api".
*/

// Salud
Route::get('/health', HealthController::class);

// CRUD completo para clientes
Route::apiResource('clients', ClientController::class);
