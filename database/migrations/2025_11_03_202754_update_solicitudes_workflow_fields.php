<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE solicitudes MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pendiente'");
        } else {
            if (!Schema::hasColumn('solicitudes', 'status_new')) {
                Schema::table('solicitudes', function (Blueprint $table) {
                    $table->string('status_new', 50)->default('pendiente')->after('status');
                });
            }

            DB::table('solicitudes')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('solicitudes')
                            ->where('id', $row->id)
                            ->update(['status_new' => $row->status]);
                    }
                });

            Schema::table('solicitudes', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('solicitudes', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });
        }

        DB::table('solicitudes')->where('status', 'vgo')->update(['status' => 'vp_mantenimiento']);
        DB::table('solicitudes')->where('status', 'compras')->update(['status' => 'compras']);
        DB::table('solicitudes')->where('status', 'normalizacion_final')->update(['status' => 'normalizacion_final']);
        DB::table('solicitudes')->where('status', 'administracion')->update(['status' => 'administracion']);
        DB::table('solicitudes')->whereNotIn('status', ['pendiente', 'vp_mantenimiento', 'vp_soporte', 'administracion', 'compras', 'normalizacion_final', 'aprobado', 'rechazado'])->update(['status' => 'pendiente']);

        if (Schema::hasColumn('solicitudes', 'destination_decision_by')) {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->dropForeign(['destination_decision_by']);
            });
        }

        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'destination_decision')) {
                $table->dropColumn([
                    'destination_decision',
                    'destination_decision_by',
                    'destination_decision_at',
                    'destination_justification',
                ]);
            }

            if (!Schema::hasColumn('solicitudes', 'approved_quantity')) {
                $table->integer('approved_quantity')->default(0)->after('quantity');
            }

            if (!Schema::hasColumn('solicitudes', 'return_date')) {
                $table->date('return_date')->nullable()->after('approved_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'approved_quantity')) {
                $table->dropColumn('approved_quantity');
            }

            if (Schema::hasColumn('solicitudes', 'return_date')) {
                $table->dropColumn('return_date');
            }

            if (!Schema::hasColumn('solicitudes', 'destination_decision')) {
                $table->enum('destination_decision', ['aprobado', 'rechazado'])->nullable()->after('status');
                $table->foreignId('destination_decision_by')->nullable()->after('destination_decision')->constrained('users')->nullOnDelete();
                $table->timestamp('destination_decision_at')->nullable()->after('destination_decision_by');
                $table->text('destination_justification')->nullable()->after('destination_decision_at');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('solicitudes')->whereNotIn('status', ['aprobado', 'rechazado'])->update(['status' => 'pendiente']);
            DB::statement("ALTER TABLE solicitudes MODIFY status ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente'");
        } else {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->enum('status_old', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente')->after('status');
            });

            DB::table('solicitudes')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $fallback = in_array($row->status, ['aprobado', 'rechazado'], true) ? $row->status : 'pendiente';
                        DB::table('solicitudes')
                            ->where('id', $row->id)
                            ->update(['status_old' => $fallback]);
                    }
                });

            Schema::table('solicitudes', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('solicitudes', function (Blueprint $table) {
                $table->renameColumn('status_old', 'status');
            });
        }
    }
};
