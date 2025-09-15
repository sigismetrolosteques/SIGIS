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
        // Cambiar las columnas para permitir valores nulos
        $table->string('security_question_1')->nullable()->change();
        $table->string('security_answer_1')->nullable()->change();
        $table->string('security_question_2')->nullable()->change();
        $table->string('security_answer_2')->nullable()->change();
        $table->string('security_question_3')->nullable()->change();
        $table->string('security_answer_3')->nullable()->change();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        // Revertir los cambios si es necesario
        $table->string('security_question_1')->nullable(false)->change();
        $table->string('security_answer_1')->nullable(false)->change();
        $table->string('security_question_2')->nullable(false)->change();
        $table->string('security_answer_2')->nullable(false)->change();
        $table->string('security_question_3')->nullable(false)->change();
        $table->string('security_answer_3')->nullable(false)->change();
    });
}
};
