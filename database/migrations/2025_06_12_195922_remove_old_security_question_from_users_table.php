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
        // Check if column exists before dropping
        if (Schema::hasColumn('users', 'security_question')) {
            $table->dropColumn('security_question');
        }
    });
}

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('security_question');
            $table->string('security_answer');
        });
    }

};
