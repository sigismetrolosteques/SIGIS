<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });

        DB::table('products')->orderBy('id')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['code' => $this->generateUniqueCode()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(uniqid('PRD-'));
        } while (DB::table('products')->where('code', $code)->exists());

        return $code;
    }
};
