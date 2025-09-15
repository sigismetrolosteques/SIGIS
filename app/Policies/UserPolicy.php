<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, User $model)
    {
        return $user->hasRole('super_admin') || $user->id === $model->id;
    }

    public function updateRoles(User $user, User $model)
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, User $model)
    {
        return $user->hasRole('super_admin') && $user->id !== $model->id;
    }
}