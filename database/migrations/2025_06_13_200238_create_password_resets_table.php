<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();  // Añadido index para mejor performance
            $table->timestamp('created_at')->nullable();
            
            // Para soportar múltiples intentos (opcional)
            $table->id(); // Añade un ID autoincremental
        });
        
        // Para MySQL/PostgreSQL puedes añadir esto:
        Schema::table('password_resets', function (Blueprint $table) {
            $table->index(['email', 'token']); // Índice compuesto
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};