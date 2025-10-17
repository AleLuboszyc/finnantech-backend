<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SaldoController extends Controller
{
    /**
     * Crea un saldo simulado de ARS para el usuario autenticado.
     */
    public function cargarSaldoSimulado(Request $request)
    {
        $user = $request->user();
        $moneda = 'ARS';
        $montoSimulado = 1000000;

        // 1. Verificar si el usuario ya tiene saldo en ARS
        $saldoARS = $user->saldos()->where('moneda', $moneda)->first();

        if ($saldoARS) {
            // Si ya tiene, devolvemos un error para no cargarle múltiples veces
            return response()->json([
                'message' => 'Error: Ya posees saldo en ARS.'
            ], 400); // 400 Bad Request
        }

        // 2. Si no tiene, se lo creamos
        $nuevoSaldo = $user->saldos()->create([
            'moneda' => $moneda,
            'cantidad' => $montoSimulado
        ]);

        return response()->json([
            'message' => '¡$1,000,000 ARS cargados exitosamente!',
            'saldo' => $nuevoSaldo
        ], 201); // 201 Created
    }
}