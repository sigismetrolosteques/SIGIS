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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sub_area')->nullable()->after('name');
            $table->json('ubicaciones')->nullable()->after('sub_area');
            $table->string('tipo_material')->nullable()->after('ubicaciones');
            $table->text('marcas_sugeridas')->nullable()->after('tipo_material');
            $table->string('modelo')->nullable()->after('marcas_sugeridas');
            $table->string('dimensiones')->nullable()->after('modelo');
            $table->text('especificaciones_tecnicas')->nullable()->after('dimensiones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sub_area',
                'ubicaciones',
                'tipo_material',
                'marcas_sugeridas',
                'modelo',
                'dimensiones',
                'especificaciones_tecnicas',
            ]);
        });
    }
};
