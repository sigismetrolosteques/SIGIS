<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Inertia\Inertia;
class SecurityAnswerController extends Controller
{
    public function verify(Request $request)
{
    $request->validate([
        'email' => ['required','email', 'exists:users,email'],
        'answer_1' => ['required','string'],
        'answer_2' => ['required','string'],
        'answer_3' => ['required','string'],
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || 
        !Hash::check($request->answer_1, $user->security_answer_1) ||
        !Hash::check($request->answer_2, $user->security_answer_2) ||
        !Hash::check($request->answer_3, $user->security_answer_3)) {
        return back()->withErrors(['email' => 'Credenciales incorrectas']);
    }

    $token = Password::createToken($user);

    return Inertia::render('Auth/ResetPassword', [
        'token' => $token,
        'email' => $user->email,
    ]);
}
}