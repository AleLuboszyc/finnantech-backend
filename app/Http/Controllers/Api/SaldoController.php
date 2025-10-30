<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SaldoController extends Controller
{
   
    public function cargarSaldoSimulado(Request $request)
    {
        //Obtener el usuario autenticado asume que el middleware auth:sanctum está aplicado
        $user = $request->user(); 
        $moneda = 'ARS';
        $montoSimulado = 1000000; 

        //Buscar si el usuario ya tiene un registro de saldo para la moneda ARS
        $saldoARS = $user->saldos()->where('moneda', $moneda)->first();

        $mensaje = '';
        $statusCode = 200; 
        $saldoResultado = null;

        if ($saldoARS) {
            //El registro de saldo ARS 
            
            if ($saldoARS->cantidad > 0) {
                return response()->json([
                    'message' => 'Error: Ya posees saldo positivo en ARS.'
                ], 400); 
            
            } else {
                $saldoARS->update(['cantidad' => $montoSimulado]);
                $mensaje = '¡$1,000,000 ARS cargados exitosamente (saldo actualizado)!';
                $statusCode = 200; 
                $saldoResultado = $saldoARS->fresh(); 
            }

        } else {
            
            //Creamos un nuevo registro de saldo para ARS
            $saldoResultado = $user->saldos()->create([
                'moneda' => $moneda,
                'cantidad' => $montoSimulado
            ]);
            $mensaje = '¡$1,000,000 ARS cargados exitosamente (saldo creado)!';
            $statusCode = 201; 
        }

        //Devolvemos la respuesta (sea de creación o actualización)
        return response()->json([
            'message' => $mensaje,
            'saldo' => $saldoResultado 
        ], $statusCode); 
    }
}
