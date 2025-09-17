<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('from_gerencia_id')->constrained('gerencias');
            $table->foreignId('to_gerencia_id')->constrained('gerencias');
            $table->enum('status', ['pendiente','aprobado','rechazado'])->default('pendiente');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
