<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    protected function authenticated(Request $request, $user)
    {
        // Si es super_admin, redirigir directamente
        if ($user->hasRole('super_admin')) {
            return redirect()->intended(RouteServiceProvider::SUPER_ADMIN_HOME);
        }

        // Para otros usuarios, verificar primer login
        if ((bool)$user->is_first_login === true || 
            empty($user->security_question_1) || 
            empty($user->security_answer_1)) {
            
            Auth::logout();
            $request->session()->put('first_login_user_id', $user->id);
            return Inertia::location(route('first.login'));
        }

        // Redirección normal para usuarios admin/otros
        return redirect()->intended(RouteServiceProvider::ADMIN_HOME);
    }

    public function __construct()
    {
        // Constructor vacío o lógica adicional si es necesaria
    }
}