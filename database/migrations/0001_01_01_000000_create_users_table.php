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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // CAMBIO: de 'name' a 'nombre'
            $table->string('apellido'); // NUEVO
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('dni')->unique()->nullable(); // NUEVO (nullable por si acaso)
            $table->date('fecha_nacimiento')->nullable(); // NUEVO
            $table->string('telefono')->nullable(); // NUEVO
            $table->string('sexo')->nullable(); // NUEVO (ej: 'masculino', 'femenino', 'otro')
            $table->string('avatar_url')->nullable(); // NUEVO (para la foto de perfil del Paso 2)
            $table->rememberToken();
            $table->timestamps();
        });
    

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
