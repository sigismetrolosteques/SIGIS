<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
public function store(LoginRequest $request)
{
    $credentials = $request->only('email', 'password');
    
    if (!Auth::attempt($credentials)) {
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no son correctas.',
        ]);
    }

    $user = Auth::user();
    
    // Verificación exhaustiva del estado de primer login
    if ($user->is_first_login == 1 || 
        empty($user->security_question_1) || 
        empty($user->security_answer_1)) {
        
        Auth::logout();
        
        // Redirección directa con parámetros en la URL
        return redirect()->route('first.login', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    $request->session()->regenerate();

    return redirect()->intended(
        $user->hasRole('super_admin') 
            ? RouteServiceProvider::SUPER_ADMIN_HOME 
            : RouteServiceProvider::ADMIN_HOME
    );
}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();  // Correcto
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
