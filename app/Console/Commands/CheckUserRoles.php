<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CheckUserRoles extends Command
{
    protected $signature = 'roles:check';
    protected $description = 'Verifica y corrige roles de usuarios';

    public function handle()
    {
        // Crear roles si no existen
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Eliminar rol antiguo si existe
        $oldRole = Role::where('name', 'super admin')->first();
        if ($oldRole) {
            $this->info('Eliminando rol obsoleto: super admin');
            $oldRole->delete();
        }
        
        // Corregir todos los usuarios
        $users = User::all();
        $corrections = [
            1 => ['super_admin'],
            2 => ['admin'],
            3 => ['admin'], // Asignar rol admin
            4 => ['admin'],
            5 => ['admin'],
        ];
        
        foreach ($corrections as $userId => $roles) {
            $user = User::find($userId);
            if ($user) {
                $user->syncRoles($roles);
                $this->info("Actualizado usuario $userId: " . implode(', ', $roles));
            }
        }
        
        // Listar usuarios con roles
        $users = User::with('roles')->get();
        
        $this->table(
            ['ID', 'Nombre', 'Email', 'Roles'],
            $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->join(', ')
                ];
            })
        );
        
        $this->info('Roles corregidos exitosamente');
        return 0;
    }
}