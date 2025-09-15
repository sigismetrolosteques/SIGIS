<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ProfileController extends Controller
{
public function edit(Request $request): Response
{
    $user = $request->user();
    
    $predefinedQuestions = [
        '¿Cuál es el nombre de tu primera mascota?',
        '¿En qué ciudad naciste?',
        '¿Cuál es tu libro favorito?',
        '¿Nombre de tu escuela primaria?',
        '¿Cuál es tu comida favorita?',
        '¿Modelo de tu primer auto?',
                // Agrega más según necesites
    ];

    return Inertia::render('Profile/Edit', [
        'mustVerifyEmail' => $user instanceof MustVerifyEmail,
        'status' => session('status'),
        'success' => session('success'),
        'error' => session('error'),
        'securityQuestions' => [
            'question_1' => $user->security_question_1 ?? null,
            'question_2' => $user->security_question_2 ?? null,
            'question_3' => $user->security_question_3 ?? null,
        ],
        'predefinedQuestions' => $predefinedQuestions, // Asegúrate de enviar esto
    ]);
}

    // Actualizar información básica del perfil
 public function updateInfo(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
    ]);

    $user = $request->user();
    $user->name = $request->name;
    
    if ($user->email !== $request->email) {
        $user->email = $request->email;
        $user->email_verified_at = null;
    }

    $user->save();

    return back()->with('success', 'Información actualizada correctamente');
}

    // Actualizar contraseña
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => ['required', 'current_password'],
        'password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
            'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[*.\-$#]).+$/'
        ],
    ]);

    $user = $request->user();
    $user->password = Hash::make($request->password);
    $user->save();

    return back()->with('success', 'Contraseña actualizada correctamente');
}

public function updateSecurityQuestions(Request $request)
{
    $validated = $request->validate([
        'question_1' => ['required', 'string', 'max:255'],
        'answer_1' => ['required', 'string', 'max:255'], // Cambiado de 'nullable' a 'required'
        'question_2' => ['required', 'string', 'max:255'],
        'answer_2' => ['required', 'string', 'max:255'], // Cambiado de 'nullable' a 'required'
        'question_3' => ['required', 'string', 'max:255'],
        'answer_3' => ['required', 'string', 'max:255'], // Cambiado de 'nullable' a 'required'
    ]);

    $user = $request->user();
    
    // Normalización de respuestas (igual que en ForgotPasswordController)
    $normalize = function($value) {
        $value = trim($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/', ' ', $value);
        return iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    };

    // Actualización directa (como en FirstLoginController)
    $user->update([
        'security_question_1' => $validated['question_1'],
        'security_answer_1' => Hash::make($normalize($validated['answer_1'])),
        'security_question_2' => $validated['question_2'],
        'security_answer_2' => Hash::make($normalize($validated['answer_2'])),
        'security_question_3' => $validated['question_3'],
        'security_answer_3' => Hash::make($normalize($validated['answer_3'])),
    ]);

    return back()->with('success', 'Preguntas de seguridad actualizadas correctamente');
}
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        try {
            $user = $request->user();
            Auth::logout();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')->with('success', 'Cuenta eliminada correctamente');

        } catch (\Exception $e) {
            return Redirect::route('profile.edit')->with('error', 'Error al eliminar la cuenta: ' . $e->getMessage());
        }
    }
}