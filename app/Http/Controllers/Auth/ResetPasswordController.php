<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request)
    {
        if ($request->email) {
            Session::put('password_reset_email', $request->email);
        }

        return inertia('Auth/ResetPassword', [
            'token' => $request->token,
            'email' => $request->email,
            'errors' => session()->get('errors') ? session()->get('errors')->getBag('default')->getMessages() : (object) [],
        ]);
    }

    public function reset(Request $request)
    {
        // Validación con mensajes personalizados
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:4',
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', '¡Tu contraseña ha sido restablecida con éxito!');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

}
