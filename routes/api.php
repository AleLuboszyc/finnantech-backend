<?php
use App\Http\Controllers\Api\NoticiaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CryptoController;
use App\Http\Controllers\Api\SaldoController;
use App\Http\Controllers\Api\TransaccionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// --- Rutas de Autenticación ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// --- Rutas Protegidas (Requieren autenticación) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta para obtener el perfil del usuario logueado
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);

    // <-- 2. LÍNEA NUEVA: Ruta para obtener los datos del mercado de criptos
    Route::get('/crypto/markets', [CryptoController::class, 'getMarketData']);

    Route::post('/saldos/cargar-simulado', [SaldoController::class, 'cargarSaldoSimulado']);

    Route::get('/noticias', [NoticiaController::class, 'index']); 

    Route::post('/trade/comprar', [TransaccionController::class, 'comprar']);
    Route::post('/trade/vender', [TransaccionController::class, 'vender']);
    Route::get('/transacciones', [TransaccionController::class, 'historial']);
});
