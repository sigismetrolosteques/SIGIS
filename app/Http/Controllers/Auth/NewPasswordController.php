<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\Log;

class NewPasswordController extends Controller
{
    public function create(Request $request)
    {
        return inertia('Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->email
        ]);
    }

    public function store(Request $request)
    {
        Log::debug('Solicitud de cambio de contraseña recibida', $request->all());

        // Validación básica
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:4|confirmed'
        ]);

        try {
            // Buscar usuario
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::error('Usuario no encontrado', ['email' => $request->email]);
                return back()->withErrors(['email' => 'Usuario no encontrado']);
            }

            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            Log::info('Contraseña actualizada exitosamente', ['user_id' => $user->id]);

            return redirect()->route('login')
                ->with('success', 'Contraseña actualizada correctamente');

        } catch (\Exception $e) {
            Log::error('Error al cambiar contraseña', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            return back()->withErrors(['error' => 'Error al actualizar la contraseña']);
        }
    }
}