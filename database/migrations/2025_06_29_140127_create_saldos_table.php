<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('moneda'); 
            $table->decimal('cantidad', 15, 8);
            $table->timestamps();

            // Esto es importante: asegura que un usuario solo pueda tener un saldo por cada moneda.
            $table->unique(['user_id', 'moneda']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('saldos');
    }
};
