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
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Quién hizo la transacción
            $table->string('tipo'); // 'compra_crypto', 'venta_crypto', 'transferencia_enviada', 'transferencia_recibida', 'deposito_simulado'
            $table->string('moneda_origen')->nullable(); // Ej: 'ARS'
            $table->decimal('cantidad_origen', 15, 8)->nullable(); // Ej: 100000.00
            $table->string('moneda_destino')->nullable(); // Ej: 'BTC'
            $table->decimal('cantidad_destino', 15, 8)->nullable(); // Ej: 0.0015
            $table->decimal('precio_unitario', 15, 8)->nullable(); // Precio de la cripto al momento de la operación
            $table->timestamps(); // Fecha de la transacción
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
