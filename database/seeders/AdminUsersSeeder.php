<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run()
    {
        // Crear roles si no existen
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crear usuario Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'cedula' => 'V-12345678', // Añadido campo cédula
            'password' => Hash::make('SuperAdmin123!'),
            'email_verified_at' => now(),
            'security_question_1' => 'Nombre de tu primera mascota',
            'security_answer_1' => Hash::make('Max'),
            'security_question_2' => 'Ciudad donde naciste',
            'security_answer_2' => Hash::make('Madrid'),
            'security_question_3' => 'Comida favorita',
            'security_answer_3' => Hash::make('Pizza'),
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Crear usuario Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'cedula' => 'V-87654321', // Añadido campo cédula
            'password' => Hash::make('Admin123!'),
            'email_verified_at' => now(),
            'security_question_1' => 'Nombre de tu mejor amigo de la infancia',
            'security_answer_1' => Hash::make('Carlos'),
            'security_question_2' => 'Modelo de tu primer auto',
            'security_answer_2' => Hash::make('Toyota'),
            'security_question_3' => 'Película favorita',
            'security_answer_3' => Hash::make('El Padrino'),
        ]);
        $admin->assignRole($adminRole);

        $this->command->info('Usuarios administradores creados exitosamente!');
        $this->command->info('Super Admin: superadmin@admin.com / SuperAdmin123!');
        $this->command->info('Admin: admin@admin.com / Admin123!');
    }
}