<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transaccion;
use Illuminate\Validation\ValidationException;
use App\Models\Saldo; // Asegurando el uso del modelo Saldo

class TransaccionController extends Controller
{
    /**
     * Procesa la compra de una criptomoneda.
     */
    public function comprar(Request $request)
    {
        $validatedData = $request->validate([
            'crypto_id' => 'required|string',
            'cantidad_ars' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $cryptoId = $validatedData['crypto_id'];
        $cantidadArsAGastar = $validatedData['cantidad_ars'];
        $monedaOrigen = 'ARS';
        $precioUnitarioArs = 0;

        // 1. Obtener precio y SÍMBOLO desde CoinGecko
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'ars',
                'ids' => $cryptoId,
            ]);
            $response->throw();

            $data = $response->json();

            if (empty($data)) {
                throw ValidationException::withMessages(['crypto_id' => 'Criptomoneda no encontrada.']);
            }

            $cryptoData = $data[0];
            $precioUnitarioArs = $cryptoData['current_price'];
            $simboloCrypto = strtoupper($cryptoData['symbol']);

        } catch (\Exception $e) {
            Log::error('Error al conectar con CoinGecko: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener los datos de la criptomoneda.'], 500);
        }

        if ($precioUnitarioArs <= 0) {
            return response()->json(['message' => 'El precio de la criptomoneda es inválido.'], 400);
        }

        $cantidadCryptoAComprar = $cantidadArsAGastar / $precioUnitarioArs;

        // 2. Iniciar Transacción de Base de Datos para asegurar consistencia
        DB::beginTransaction();
        try {
            // 3. Verificar y Bloquear Saldo ARS
            $saldoArs = $user->saldos()->where('moneda', $monedaOrigen)->lockForUpdate()->first();

            // ✅ LÓGICA CRÍTICA PARA EL TEST UNITARIO (Devuelve el JSON que el test espera)
            if (!$saldoArs || $saldoArs->cantidad < $cantidadArsAGastar) {
                DB::rollBack(); // Revertir la transacción antes de devolver el error

                // Devuelve el Status 422 con el JSON de error que espera la prueba unitaria
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'cantidad_ars' => ['Saldo insuficiente en ARS.']
                    ]
                ], 422);
            }

            // 4. Restar Saldo ARS
            $saldoArs->decrement('cantidad', $cantidadArsAGastar);

            // 5. Encontrar o Crear Saldo Crypto y Sumar la cantidad comprada
            $saldoCrypto = $user->saldos()->firstOrCreate(
                ['moneda' => $simboloCrypto],
                ['cantidad' => 0]
            );
            $saldoCrypto->increment('cantidad', $cantidadCryptoAComprar);

            // 6. Registrar la operación en el historial de transacciones
            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'compra_crypto',
                'moneda_origen' => $monedaOrigen,
                'cantidad_origen' => $cantidadArsAGastar,
                'moneda_destino' => $simboloCrypto,
                'cantidad_destino' => $cantidadCryptoAComprar,
                'precio_unitario' => $precioUnitarioArs,
            ]);

            // 7. Si todo salió bien, confirmar los cambios en la base de datos
            DB::commit();

            return response()->json([
                'message' => "Compra exitosa: {$cantidadCryptoAComprar} {$simboloCrypto}",
                'saldo_ars' => $saldoArs->fresh(),
                'saldo_crypto' => $saldoCrypto->fresh()
            ], 201);

        } catch (\Exception $e) {
            // 8. Si algo falló, revertir todos los cambios para no dejar datos inconsistentes
            DB::rollBack();

            // Esta sección ahora solo atrapa otros ValidationException, no el de saldo.
            if ($e instanceof ValidationException) {
                return response()->json($e->errors(), 422);
            }

            Log::error('Error en compra: ' . $e->getMessage());
            return response()->json(['message' => 'Error al procesar la compra.'], 500);
        }
    }

    /**
     * Procesa la venta de una criptomoneda.
     */
    public function vender(Request $request)
    {
        $request->validate([
            'crypto_id' => 'required|string',
            'cantidad_crypto' => 'required|numeric|min:0.00000001',
        ]);

        $user = $request->user();
        $cryptoId = $request->crypto_id;
        $cantidadCryptoAVender = $request->cantidad_crypto;
        $monedaDestino = 'ARS';

        // 1. Obtener precio y símbolo desde CoinGecko
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'ars',
                'ids' => $cryptoId,
            ]);
            $response->throw();
            $data = $response->json();
            if (empty($data)) {
                throw ValidationException::withMessages(['crypto_id' => 'Criptomoneda no encontrada.']);
            }
            $cryptoData = $data[0];
            $precioUnitarioArs = $cryptoData['current_price'];
            $simboloCrypto = strtoupper($cryptoData['symbol']);

        } catch (\Exception $e) {
            Log::error('Error al conectar con CoinGecko en venta: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener los datos de la criptomoneda.'], 500);
        }

        if ($precioUnitarioArs <= 0) {
            return response()->json(['message' => 'Precio inválido para la criptomoneda.'], 400);
        }

        $cantidadArsARecibir = $cantidadCryptoAVender * $precioUnitarioArs;

        // 2. Iniciar Transacción de Base de Datos
        DB::beginTransaction();
        try {
            // 3. Verificar y Bloquear Saldo Crypto
            $saldoCrypto = $user->saldos()->where('moneda', $simboloCrypto)->lockForUpdate()->first();

            if (!$saldoCrypto || $saldoCrypto->cantidad < $cantidadCryptoAVender) {
                throw ValidationException::withMessages(['cantidad_crypto' => 'Saldo insuficiente en ' . $simboloCrypto]);
            }

            // 4. Restar Saldo Crypto
            $saldoCrypto->decrement('cantidad', $cantidadCryptoAVender);

            // 5. Encontrar o Crear Saldo ARS y Sumar
            $saldoArs = $user->saldos()->firstOrCreate(['moneda' => $monedaDestino], ['cantidad' => 0]);
            $saldoArs->increment('cantidad', $cantidadArsARecibir);

            // 6. Registrar la Transacción
            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'venta_crypto',
                'moneda_origen' => $simboloCrypto,
                'cantidad_origen' => $cantidadCryptoAVender,
                'moneda_destino' => $monedaDestino,
                'cantidad_destino' => $cantidadArsARecibir,
                'precio_unitario' => $precioUnitarioArs,
            ]);

            // 7. Confirmar
            DB::commit();

            return response()->json([
                'message' => "Venta exitosa: Recibiste {$cantidadArsARecibir} ARS",
                'saldo_ars' => $saldoArs->fresh(),
                'saldo_crypto' => $saldoCrypto->fresh()
            ], 201);

        } catch (\Exception $e) {
            // 8. Revertir si algo falla
            DB::rollBack();
            if ($e instanceof ValidationException) {
                return response()->json($e->errors(), 422);
            }
            Log::error('Error en venta: ' . $e->getMessage());
            return response()->json(['message' => 'Error al procesar la venta.'], 500);
        }
    }

    /**
     * Muestra el historial de transacciones del usuario.
     */
     public function historial(Request $request)
     {
        $user = $request->user();
        $transacciones = $user->transacciones()->latest()->paginate(20);
        return response()->json($transacciones);
     }
}