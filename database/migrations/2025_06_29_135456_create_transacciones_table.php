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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo'); // 'compra_crypto', 'venta_crypto', etc.
            
            // --- COLUMNAS CORREGIDAS ---
            $table->string('moneda_origen')->nullable();      // Ej: 'ARS'
            $table->decimal('cantidad_origen', 20, 8)->nullable(); // Ej: 50000.00
            $table->string('moneda_destino')->nullable();     // Ej: 'BTC'
            $table->decimal('cantidad_destino', 20, 8)->nullable(); // Ej: 0.00150000
            $table->decimal('precio_unitario', 20, 8)->nullable(); // Precio de la cripto en ese momento
            // --- FIN CORRECCIÓN ---

            $table->timestamps();
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