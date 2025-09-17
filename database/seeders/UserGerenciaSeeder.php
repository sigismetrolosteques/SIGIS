<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Gerencia;

class UserGerenciaSeeder extends Seeder
{
    public function run(): void
    {
        $gerencias = Gerencia::all();

        // Asigna a cada usuario una gerencia aleatoria
        User::all()->each(function ($user) use ($gerencias) {
            $user->gerencia_id = $gerencias->random()->id;
            $user->save();
        });
    }
}
