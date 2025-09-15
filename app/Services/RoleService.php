<?php
namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleService
{
    public function getRoles()
    {
        return Role::all(); // ✅ debe devolver una colección, no un único Role
    }
}

