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
            'Administración',
            'VGO',
            'Normalización',
            'Obras Civiles',
            'Electrificación',
            'Vías Férreas',
            'Señalización',
            'Material Rodante',
            'Tecnología',
            'Electromecánica',
        ];

        $existingIds = DB::table('gerencias')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        foreach ($names as $index => $name) {
            if (isset($existingIds[$index])) {
                DB::table('gerencias')
                    ->where('id', $existingIds[$index])
                    ->update([
                        'name' => $name,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('gerencias')->insert([
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
