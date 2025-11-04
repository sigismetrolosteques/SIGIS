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
        Schema::table('solicitud_status_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitud_status_logs', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('justification');
            }

            if (!Schema::hasColumn('solicitud_status_logs', 'approved_quantity')) {
                $table->integer('approved_quantity')->nullable()->after('attachment_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_status_logs', function (Blueprint $table) {
            if (Schema::hasColumn('solicitud_status_logs', 'approved_quantity')) {
                $table->dropColumn('approved_quantity');
            }

            if (Schema::hasColumn('solicitud_status_logs', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
        });
    }
};
