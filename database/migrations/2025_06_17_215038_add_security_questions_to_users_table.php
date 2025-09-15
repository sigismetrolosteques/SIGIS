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
    // Verificar si las columnas ya existen antes de crearlas
    if (!Schema::hasColumn('users', 'security_question_1')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('security_question_1')->nullable()->after('password');
            $table->string('security_answer_1')->nullable()->after('security_question_1');
            $table->string('security_question_2')->nullable()->after('security_answer_1');
            $table->string('security_answer_2')->nullable()->after('security_question_2');
            $table->string('security_question_3')->nullable()->after('security_answer_2');
            $table->string('security_answer_3')->nullable()->after('security_question_3');
        });
    }
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'security_question_1',
            'security_answer_1',
            'security_question_2',
            'security_answer_2',
            'security_question_3',
            'security_answer_3',
        ]);
    });
}
};
