<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'user' => auth()->user()->only('id', 'name', 'email', 'role'),
            'isSuperAdmin' => auth()->user()->hasRole('super_admin')
        ]);
    }
}