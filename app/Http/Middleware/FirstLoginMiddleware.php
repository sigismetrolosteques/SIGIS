<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FirstLoginMiddleware
{
public function handle(Request $request, Closure $next)
{
    if (!Auth::check()) {
        return $next($request);
    }

    $user = Auth::user();
    
    if (!$user->hasRole('super_admin') &&
        ($user->is_first_login == 1 || 
         empty($user->security_question_1))) {
        
        if (!$request->is('first-login*')) {
            return redirect()->route('first.login', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }
    }

    return $next($request);
}
}
