<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'security_question_1',
                'security_question_2',
                'security_question_3',
                'security_answer_1',
                'security_answer_2',
                'security_answer_3',
            ];

            foreach ($columns as $column) {
                if (!Schema::hasColumn('users', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
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
