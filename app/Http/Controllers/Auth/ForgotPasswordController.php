<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ForgotPasswordController extends Controller
{
   
    public function showLinkRequestForm(Request $request)
    {
        Log::info('Mostrando formulario de email para recuperación');
        
        // Pasar errores de sesión a la vista
        return inertia('Auth/ForgotPassword', [
            'errors' => $request->session()->get('errors')?->getBag('default')->getMessages()
        ]);
    }

    public function handleEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        Log::info('Email recibido: ' . $request->email);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            Log::error('Usuario no encontrado para email: ' . $request->email);
            return back()->withErrors([
                'email' => 'El correo electrónico no está registrado en nuestro sistema'
            ]);
        }
        
        Session::put('password_reset_email', $user->email);
        Log::info('Email guardado en sesión: ' . $user->email);
        
        return redirect()->route('password.questions');
    }

     public function showSecurityQuestions(Request $request)
    {
        $email = Session::get('password_reset_email');
        
        if (!$email) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Sesión expirada. Por favor ingrese su email nuevamente.'
            ]);
        }
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Usuario no encontrado. Por favor intente nuevamente.'
            ]);
        }
        
        // Recuperar errores de sesión si existen
        $errors = Session::get('errors')?->getBag('default')->getMessages() ?? [];
        
        return inertia('Auth/SecurityQuestions', [
            'email' => $user->email,
            'securityQuestions' => [
                $user->security_question_1,
                $user->security_question_2,
                $user->security_question_3
            ],
            'errors' => $errors // Pasar errores a la vista
        ]);
    }

    public function verifySecurityAnswers(Request $request)
{
    Log::info('[VERIFY SECURITY] Iniciando verificación de respuestas');
    
    $request->validate([
        'answer_1' => 'required|string',
        'answer_2' => 'required|string',
        'answer_3' => 'required|string',
    ]);

    $email = Session::get('password_reset_email');
    if (!$email) {
        return redirect()->route('password.request')->withErrors([
            'email' => 'Sesión expirada. Por favor ingrese su email nuevamente.'
        ]);
    }

    $user = User::where('email', $email)->first();
    if (!$user) {
        return redirect()->route('password.request')->withErrors([
            'email' => 'Usuario no encontrado. Por favor intente nuevamente.'
        ]);
    }

    // Normalización mejorada
    $normalize = function($value) {
        // Eliminar espacios, acentos y convertir a minúsculas
        $value = trim($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/', ' ', $value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        return $value;
    };
    
    $answers = [
        'answer_1' => $normalize($request->answer_1),
        'answer_2' => $normalize($request->answer_2),
        'answer_3' => $normalize($request->answer_3)
    ];

    Log::debug('[VERIFY SECURITY] Respuestas normalizadas:', $answers);

    $fieldErrors = [];
    
    // Verificación directa sin variaciones
    if (!Hash::check($answers['answer_1'], $user->security_answer_1)) {
        $fieldErrors['answer_1'] = 'Respuesta incorrecta';
        Log::warning("[RESPUESTA] Respuesta 1 incorrecta: {$answers['answer_1']}");
    }
    
    if (!Hash::check($answers['answer_2'], $user->security_answer_2)) {
        $fieldErrors['answer_2'] = 'Respuesta incorrecta';
        Log::warning("[RESPUESTA] Respuesta 2 incorrecta: {$answers['answer_2']}");
    }
    
    if (!Hash::check($answers['answer_3'], $user->security_answer_3)) {
        $fieldErrors['answer_3'] = 'Respuesta incorrecta';
        Log::warning("[RESPUESTA] Respuesta 3 incorrecta: {$answers['answer_3']}");
    }

    if (!empty($fieldErrors)) {
        Log::error('[VERIFY SECURITY] Respuestas incorrectas para usuario: ' . $user->email);
        return back()
            ->withErrors($fieldErrors)
            ->with('generalError', 'Una o más respuestas son incorrectas. Por favor inténtelo de nuevo.');
    }

    Log::info('[VERIFY SECURITY] Todas las respuestas correctas');
    $token = Password::createToken($user);
    Session::forget('password_reset_email');
    
    return redirect()->route('password.reset', [
        'token' => $token,
        'email' => $user->email
    ]);
}
}