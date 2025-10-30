<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User; 
use App\Models\Saldo; 
use App\Models\Transaccion; 
use Laravel\Sanctum\Sanctum; 

class TradingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba el rechazo de compra por falta de saldo en ARS.
     * Verifica que el controlador devuelva un 422 y que la BD no se modifique.
     * @return void
     */
    public function test_saldo_insuficiente_valida(): void
    {
        //Preparación: Crear un usuario autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        //CORRECCIÓN de UNIQUE: Buscamos o creamos el saldo ARS inicial en 0.
        $saldoArs = Saldo::firstOrCreate(
            ['user_id' => $user->id, 'moneda' => 'ARS'],
            ['cantidad' => 0.00]
        );

        //ACTUALIZAR: Establecer el saldo de prueba BAJO (100 ARS).
        $saldoArs->update(['cantidad' => 100.00]);
        
        //Definir la transacción que va a exceder el saldo (500 ARS)
        $montoAComprar = 500.00; 
        $datosCompra = [
            'crypto_id' => 'bitcoin',
            //CORRECCIÓN FINAL: Usar 'cantidad_ars' para coincidir con el controlador
            'cantidad_ars' => $montoAComprar, 
        ];

        //Ejecutar la petición POST de compra
        $response = $this->postJson('/api/trade/comprar', $datosCompra);

        //Verificaciones
        
        //El status HTTP debe ser 422, ya que tu controlador lanza ValidationException.
        $response->assertStatus(422); 
        
        //Verifica el error de validación específico que lanza el controlador
        $response->assertJsonValidationErrors([
            'cantidad_ars' => 'Saldo insuficiente en ARS.'
        ]);

        //VERIFICACIÓN CRÍTICA: El saldo en la tabla 'saldos' NO debe haber cambiado.
        $this->assertDatabaseHas('saldos', [
            'user_id' => $user->id,
            'moneda' => 'ARS',
            'cantidad' => 100.00, // Debe ser el monto inicial de 100.00
        ]);
        
        //VERIFICACIÓN CRÍTICA: NO debe haberse creado ninguna transacción
        $this->assertDatabaseCount('transacciones', 0);
    }
}