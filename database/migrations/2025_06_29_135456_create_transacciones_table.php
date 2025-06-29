<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id(); // Crea una columna 'id' única y autoincremental.

            // Crea una columna para la ID del usuario y la enlaza con la tabla 'users'.
            $table->foreignId('user_id')->constrained('users');

            // Define el tipo de transacción. Solo puede tener uno de estos valores.
            $table->enum('tipo', ['ingreso', 'transferencia_enviada', 'transferencia_recibida', 'compra_crypto', 'venta_crypto']);

            // El monto de la transacción. 15 dígitos en total, 8 de ellos para decimales (ideal para criptos).
            $table->decimal('monto', 15, 8);

            // La moneda o criptomoneda de la transacción (ej: 'ARS', 'BTC', 'USD').
            $table->string('moneda');

            // La ID del usuario de destino (para transferencias). Puede ser nulo si no es una transferencia.
            $table->foreignId('destinatario_id')->nullable()->constrained('users');

            $table->timestamps(); // Crea automáticamente las columnas 'created_at' y 'updated_at'.
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
