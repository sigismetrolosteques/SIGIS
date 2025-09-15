<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('cedula')->nullable()->after('email');
    });

    // Actualizar registros existentes con un valor por defecto
    DB::table('users')->update(['cedula' => 'V-00000000']);

    // Ahora hacer que la columna no sea nullable
    Schema::table('users', function (Blueprint $table) {
        $table->string('cedula')->nullable(false)->change();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('cedula');
    });
}
};
