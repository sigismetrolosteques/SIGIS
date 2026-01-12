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

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE solicitudes DROP FOREIGN KEY solicitudes_to_gerencia_id_foreign');
            DB::statement('ALTER TABLE solicitudes MODIFY to_gerencia_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE solicitudes ADD CONSTRAINT solicitudes_to_gerencia_id_foreign FOREIGN KEY (to_gerencia_id) REFERENCES gerencias(id)');
            return;
        }

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            if (Schema::hasTable('solicitudes_tmp')) {
                Schema::drop('solicitudes_tmp');
            }

            Schema::create('solicitudes_tmp', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('inventory_id')->nullable()->constrained('inventories');
                $table->integer('quantity')->default(1);
                $table->integer('approved_quantity')->default(0);
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('from_gerencia_id')->constrained('gerencias');
                $table->foreignId('to_gerencia_id')->nullable()->constrained('gerencias');
                $table->string('status', 50)->default('pendiente');
                $table->text('description')->nullable();
                $table->date('return_date')->nullable();
                $table->timestamps();
            });

            DB::table('solicitudes')
                ->select([
                    'id',
                    'code',
                    'product_id',
                    'inventory_id',
                    'quantity',
                    'approved_quantity',
                    'created_by',
                    'from_gerencia_id',
                    'to_gerencia_id',
                    'status',
                    'description',
                    'return_date',
                    'created_at',
                    'updated_at',
                ])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('solicitudes_tmp')->insert([
                            'id' => $row->id,
                            'code' => $row->code,
                            'product_id' => $row->product_id,
                            'inventory_id' => $row->inventory_id,
                            'quantity' => $row->quantity,
                            'approved_quantity' => $row->approved_quantity,
                            'created_by' => $row->created_by,
                            'from_gerencia_id' => $row->from_gerencia_id,
                            'to_gerencia_id' => $row->to_gerencia_id,
                            'status' => $row->status,
                            'description' => $row->description,
                            'return_date' => $row->return_date,
                            'created_at' => $row->created_at,
                            'updated_at' => $row->updated_at,
                        ]);
                    }
                });

            Schema::drop('solicitudes');
            Schema::rename('solicitudes_tmp', 'solicitudes');
            Schema::enableForeignKeyConstraints();
            return;
        }

        if (!Schema::hasColumn('solicitudes', 'to_gerencia_id_new')) {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->unsignedBigInteger('to_gerencia_id_new')->nullable()->after('to_gerencia_id');
            });
        }

        DB::table('solicitudes')
            ->select(['id', 'to_gerencia_id'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('solicitudes')
                        ->where('id', $row->id)
                        ->update(['to_gerencia_id_new' => $row->to_gerencia_id]);
                }
            });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn('to_gerencia_id');
        });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->renameColumn('to_gerencia_id_new', 'to_gerencia_id');
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE solicitudes DROP FOREIGN KEY solicitudes_to_gerencia_id_foreign');
            DB::statement('ALTER TABLE solicitudes MODIFY to_gerencia_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE solicitudes ADD CONSTRAINT solicitudes_to_gerencia_id_foreign FOREIGN KEY (to_gerencia_id) REFERENCES gerencias(id)');
            return;
        }
    }
};
