<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse;

class CustomAuthenticatedSessionController extends Controller
{
public function store(Request $request)
{
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $requiresSetup = !$user->hasRole('super_admin') &&
                         ($user->is_first_login ||
                          empty($user->security_question_1) ||
                          empty($user->security_answer_1));
        
        if ($requiresSetup) {
            Auth::logout();
            return Inertia::render('Auth/FirstLogin', [
                'email' => $user->email,
                'user_id' => $user->id,
                'requires_setup' => true
            ]);
        }
        
        return app(LoginResponse::class);
    }
    
    return back()->withErrors(['email' => 'Credenciales incorrectas']);
}
}
