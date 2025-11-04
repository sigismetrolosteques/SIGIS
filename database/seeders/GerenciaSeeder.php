<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Gerencia;

class GerenciaSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Administración',
            'Normalización',
            'VP. Mantenimiento',
            'VP. Soporte',
            'Compras',
            'Obras Civiles',
            'Electrificación',
            'Vías Férreas',
            'Señalización',
            'Material Rodante',
            'Tecnología',
            'Electromecánica',
        ];

        DB::transaction(function () use ($names) {
            $gerencias = Gerencia::orderBy('id')->get();

            foreach ($gerencias as $gerencia) {
                $gerencia->update(['name' => '__tmp_' . $gerencia->id]);
            }

            foreach ($names as $index => $name) {
                if (isset($gerencias[$index])) {
                    $gerencias[$index]->update(['name' => $name]);
                } else {
                    Gerencia::create(['name' => $name]);
                }
            }
        });
    }
}
