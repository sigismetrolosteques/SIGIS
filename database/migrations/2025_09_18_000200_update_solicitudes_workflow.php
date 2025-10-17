<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE solicitudes MODIFY status ENUM('pendiente','vgo','administracion','aprobado','rechazado') DEFAULT 'pendiente'");
        }

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->enum('destination_decision', ['aprobado', 'rechazado'])->nullable()->after('status');
            $table->foreignId('destination_decision_by')->nullable()->after('destination_decision')->constrained('users')->nullOnDelete();
            $table->timestamp('destination_decision_at')->nullable()->after('destination_decision_by');
            $table->text('destination_justification')->nullable()->after('destination_decision_at');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropForeign(['destination_decision_by']);
            $table->dropColumn([
                'destination_decision',
                'destination_decision_by',
                'destination_decision_at',
                'destination_justification',
            ]);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE solicitudes MODIFY status ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente'");
        }
    }
};
