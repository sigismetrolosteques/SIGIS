<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gerencia_id')) {
                $table->foreignId('gerencia_id')
                      ->nullable()
                      ->constrained('gerencias')
                      ->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'gerencia_id')) {
                $table->dropForeign(['gerencia_id']);
                $table->dropColumn('gerencia_id');
            }
        });
    }
};
