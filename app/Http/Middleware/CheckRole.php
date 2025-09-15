<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CheckRole
{
public function handle(Request $request, Closure $next, ...$roles)
{
    if (!auth()->check()) {
        abort(401, 'No autenticado');
    }

    $userId = auth()->id();

    $hasRole = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_has_roles.model_id', $userId)
        ->whereIn('roles.name', $roles) // ✅ Acepta múltiples roles
        ->exists();

    if (!$hasRole) {
        abort(403, 'No autorizado');
    }

    return $next($request);
}

}