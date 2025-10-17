<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\DB; // ¡Importante para transacciones!
use Illuminate\Support\Facades\Http; // Para llamar a CoinGecko
use App\Models\Saldo;
use App\Models\Transaccion;
use Illuminate\Validation\ValidationException;

class TransaccionController extends Controller
{
    // --- FUNCIÓN DE COMPRA ---
    public function comprar(Request $request)
    {
        $request->validate([
            'crypto_id' => 'required|string', // Ej: 'bitcoin', 'ethereum'
            'cantidad_ars' => 'required|numeric|min:1', // Cuántos ARS quiere gastar
        ]);

        $user = $request->user();
        $cryptoId = $request->crypto_id;
        $cantidadArsAGastar = $request->cantidad_ars;
        $monedaOrigen = 'ARS';

        // 1. Obtener precio actual de la cripto desde CoinGecko
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $cryptoId,
                'vs_currencies' => 'ars',
            ]);
            $response->throw(); // Lanza excepción si hay error HTTP
            
            if (!isset($response->json()[$cryptoId]['ars'])) {
                 throw ValidationException::withMessages(['crypto_id' => 'No se pudo obtener el precio para esta criptomoneda.']);
            }
            $precioUnitarioArs = $response->json()[$cryptoId]['ars'];

        } catch (\Exception $e) {
             return response()->json(['message' => 'Error al obtener el precio de la criptomoneda.'], 500);
        }

        if ($precioUnitarioArs <= 0) {
             return response()->json(['message' => 'Precio inválido para la criptomoneda.'], 400);
        }
        
        $cantidadCryptoAComprar = $cantidadArsAGastar / $precioUnitarioArs;
        $simboloCrypto = strtoupper($cryptoId); // Ej: 'BTC' (asumiendo que el id es 'bitcoin') - Mejorar esto si se puede

        // 2. Iniciar Transacción de Base de Datos
        DB::beginTransaction();
        try {
            // 3. Verificar y Bloquear Saldo ARS (para evitar concurrencia)
            $saldoArs = $user->saldos()->where('moneda', $monedaOrigen)->lockForUpdate()->first();

            if (!$saldoArs || $saldoArs->cantidad < $cantidadArsAGastar) {
                throw ValidationException::withMessages(['cantidad_ars' => 'Saldo insuficiente en ARS.']);
            }

            // 4. Restar Saldo ARS
            $saldoArs->decrement('cantidad', $cantidadArsAGastar);

            // 5. Encontrar o Crear Saldo Crypto y Sumar
            $saldoCrypto = $user->saldos()->firstOrCreate(
                ['moneda' => $simboloCrypto], // Busca por moneda
                ['cantidad' => 0]              // Si no existe, lo crea con 0
            );
            $saldoCrypto->increment('cantidad', $cantidadCryptoAComprar);

            // 6. Registrar la Transacción
            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'compra_crypto',
                'moneda_origen' => $monedaOrigen,
                'cantidad_origen' => $cantidadArsAGastar,
                'moneda_destino' => $simboloCrypto,
                'cantidad_destino' => $cantidadCryptoAComprar,
                'precio_unitario' => $precioUnitarioArs,
            ]);

            // 7. Confirmar Transacción de Base de Datos
            DB::commit();

            return response()->json([
                'message' => "Compra exitosa: {$cantidadCryptoAComprar} {$simboloCrypto}",
                'saldo_ars' => $saldoArs->fresh(), // Devolvemos los saldos actualizados
                'saldo_crypto' => $saldoCrypto->fresh()
            ], 201);

        } catch (\Exception $e) {
            // 8. Si algo falla, Revertir Todo
            DB::rollBack();
            
            // Si es un error de validación nuestro, lo devolvemos tal cual
             if ($e instanceof ValidationException) {
                return response()->json($e->errors(), 422);
            }
            // Si es otro error, devolvemos un 500 genérico
            Log::error("Error en compra: " . $e->getMessage()); // ✅ CORREGIDO: se eliminó el backslash (\)
            return response()->json(['message' => 'Error al procesar la compra.'], 500);
        }
    }

    // --- FUNCIÓN DE VENTA (Es similar pero al revés) ---
    public function vender(Request $request)
    {
       // ... (Implementación similar a comprar, pero verificando saldo crypto y sumando ARS)
       // ¡Esta queda como tarea para Irma y Rocio! 😉
       // Tienen que:
       // 1. Validar 'crypto_id' y 'cantidad_crypto' a vender.
       // 2. Obtener precio de CoinGecko.
       // 3. Calcular cuántos ARS recibirá.
       // 4. Iniciar DB::beginTransaction().
       // 5. Verificar y bloquear saldo crypto.
       // 6. Restar saldo crypto.
       // 7. Encontrar o crear saldo ARS y sumar.
       // 8. Registrar transacción tipo 'venta_crypto'.
       // 9. DB::commit() o DB::rollBack().
       // 10. Devolver respuesta.
        return response()->json(['message' => 'Funcionalidad de venta no implementada aún.'], 501); // 501 Not Implemented
    }
    
    // --- FUNCIÓN PARA VER HISTORIAL ---
     public function historial(Request $request)
     {
        $user = $request->user();
        // Obtenemos las últimas 20 transacciones del usuario, ordenadas por fecha
        $transacciones = $user->transacciones()->latest()->paginate(20); 
        return response()->json($transacciones);
     }
}