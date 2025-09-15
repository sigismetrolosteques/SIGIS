<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Inertia\Inertia;
class FirstLoginController extends Controller
{
public function showForm(Request $request)
{
    if (!$request->has('user_id')) {
        return redirect('/login')->with('error', 'Acceso no autorizado para configuración inicial');
    }

    return view('auth.first-login', [
        'email' => $request->email
    ]);
}

public function processFirstLogin(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'password' => 'required|confirmed|min:8',
        'security_questions' => 'required|array|size:3',
        'security_questions.*.question' => 'required|string',
        'security_questions.*.answer' => 'required|string'
    ]);

    $user = User::find($validated['user_id']);
    
    $user->update([
        'password' => Hash::make($validated['password']),
        'security_question_1' => $validated['security_questions'][0]['question'],
        'security_answer_1' => Hash::make($validated['security_questions'][0]['answer']),
        // ... repetir para las otras 2 preguntas
        'is_first_login' => false
    ]);

    Auth::login($user);

    return response()->json([
        'redirect_to' => $user->hasRole('super admin')
            ? RouteServiceProvider::SUPER_ADMIN_HOME
            : RouteServiceProvider::ADMIN_HOME
    ]);
}
public function create(Request $request)
    {
        if (!$request->has('user_id') || !$request->has('email')) {
            return redirect()->route('login')->withErrors([
                'message' => 'Acceso no autorizado al formulario de primer login'
            ]);
        }

        $user = User::find($request->input('user_id'));
        
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'message' => 'Usuario no encontrado'
            ]);
        }

        return Inertia::render('Auth/FirstLogin', [
            'email' => $request->input('email'),
            'user_id' => $request->input('user_id'),
            'default_questions' => [
                '¿Cuál es el nombre de tu primera mascota?',
                '¿En qué ciudad naciste?',
                '¿Cuál es tu comida favorita?'
            ]
        ]);
    }

   public function store(Request $request)
{
    // Validación mejorada
    $validatedData = $request->validate([
        'user_id' => 'required|exists:users,id',
        'password' => 'required|confirmed|min:8',
        'security_questions' => 'required|array|size:3',
        'security_questions.*.question' => 'required|string|max:255',
        'security_questions.*.answer' => 'required|string|max:255'
    ]);

    try {
        $user = User::findOrFail($validatedData['user_id']);
        
        $updateResult = $user->update([
            'password' => Hash::make($validatedData['password']),
            'security_question_1' => $validatedData['security_questions'][0]['question'],
            'security_answer_1' => Hash::make($validatedData['security_questions'][0]['answer']),
            'security_question_2' => $validatedData['security_questions'][1]['question'],
            'security_answer_2' => Hash::make($validatedData['security_questions'][1]['answer']),
            'security_question_3' => $validatedData['security_questions'][2]['question'],
            'security_answer_3' => Hash::make($validatedData['security_questions'][2]['answer']),
            'is_first_login' => false
        ]);

        if (!$updateResult) {
            throw new \Exception("No se pudo actualizar el usuario en la base de datos");
        }

        Auth::login($user);

        // Respuesta JSON explícita
        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada exitosamente',
            'redirect' => $user->hasRole('super admin') 
                ? RouteServiceProvider::SUPER_ADMIN_HOME 
                : RouteServiceProvider::ADMIN_HOME,
            'user' => $user->fresh()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
        ], 500);
    }
}
}