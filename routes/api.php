<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Ejemplo de una ruta protegida (la usaremos más adelante)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});