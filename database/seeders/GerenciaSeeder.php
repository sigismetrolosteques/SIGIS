<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GerenciaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $names = [
            'Gerencia de Administración',
            'Gerencia de Finanzas',
            'Gerencia de Tecnología',
            'Gerencia de Recursos Humanos',
            'Gerencia de Operaciones',
            'Gerencia de Ventas',
            'Gerencia de Compras',
            'Gerencia de Proyectos',
        ];

        foreach ($names as $name) {
            DB::table('gerencias')->updateOrInsert(
                ['name' => $name],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
