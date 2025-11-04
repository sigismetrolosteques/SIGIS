<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Gerencia;

class UserGerenciaSeeder extends Seeder
{
    protected array $adminGerencias = [
        'Administración',
        'Normalización',
        'VP. Mantenimiento',
        'VP. Soporte',
        'Compras',
    ];

    public function run(): void
    {
        $gerencias = Gerencia::all();

        // Asigna a cada usuario una gerencia aleatoria
        User::all()->each(function ($user) use ($gerencias) {
            $randomGerencia = $gerencias->random();
            $user->gerencia_id = $randomGerencia->id;
            $user->save();

            if ($user->hasRole('super_admin')) {
                return;
            }

            $role = in_array($randomGerencia->name, $this->adminGerencias, true) ? 'admin' : 'viewer';
            $user->syncRoles([$role]);
        });
    }
}
