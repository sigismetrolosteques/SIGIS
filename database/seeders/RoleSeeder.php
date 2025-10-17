<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar y crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']); // Cambiado a snake_case
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);

        // Crear permisos básicos
        Permission::firstOrCreate(['name' => 'manage_users']);
        Permission::firstOrCreate(['name' => 'edit_own_profile']);
        
        // Asignar permisos a roles
        $superAdminRole->givePermissionTo('manage_users');
        $adminRole->givePermissionTo('edit_own_profile');
        $viewerRole->givePermissionTo('edit_own_profile');
        
        // Crear usuario super admin si no existe
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        
        // Asignar rol si no lo tiene
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }
        
        // Crear usuario admin regular si no existe
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Regular',
                'password' => Hash::make('password'),
            ]
        );
        
        // Asignar rol si no lo tiene
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
        
        $this->command->info('Roles y usuarios configurados correctamente');
    }
}
