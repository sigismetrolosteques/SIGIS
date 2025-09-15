<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Services\RoleService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $authUser = auth()->user();
        
        // Verificar rol SUPER_ADMIN con consulta directa
        $isSuperAdmin = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $authUser->id)
            ->where('roles.name', 'super_admin')
            ->exists();
        
        if ($isSuperAdmin) {
            $users = User::with('roles:id,name')
                ->latest()
                ->paginate(10);
        } else {
            $users = User::where('id', $authUser->id)
                ->with('roles:id,name')
                ->paginate(1);
        }

        return Inertia::render('User/Index', [
            'users' => $users,
            'can' => [
                'create' => $isSuperAdmin,
                'edit' => true,
                'delete' => $isSuperAdmin,
            ],
            'is_super_admin' => $isSuperAdmin
        ]);
    }

    public function create()
    {
        return Inertia::render('User/Create', [
            'roles' => Role::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'cedula' => 'required|string|regex:/^[VE]-\d{7,9}$/|unique:users',
    ]);

    $tempPassword = Str::random(8);
    
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'cedula' => $request->cedula,
        'password' => Hash::make($tempPassword),
        'is_first_login' => true,
        'security_question_1' => null,
        'security_answer_1' => null,
        'security_question_2' => null,
        'security_answer_2' => null,
        'security_question_3' => null,
        'security_answer_3' => null,
    ]);

    $user->assignRole('admin');

    // Enviar correo de bienvenida
    try {
        Mail::to($user->email)->send(new WelcomeUserMail(
            $user->name,
            $user->email,
            $tempPassword
        ));
    } catch (\Exception $e) {
        Log::error('Error enviando correo de bienvenida: ' . $e->getMessage());
        // Puedes decidir si quieres continuar o retornar un error aquí
    }

    return redirect()->route('user.index')->with('success', 'Usuario creado correctamente');
}
    public function edit(User $user)
    {
        // Verificar permisos
        if (!auth()->user()->hasRole('super_admin') && auth()->id() != $user->id) {
            abort(403, 'No tienes permiso para editar este usuario');
        }

        return Inertia::render('User/Edit', [
            'user' => $user->load('roles:id,name'),
            'roles' => auth()->user()->hasRole('super_admin')
                ? app(RoleService::class)->getRoles()
                : null,
            'canEditRole' => auth()->user()->hasRole('super_admin'),
        ]);
    }

   public function update(Request $request, User $user)
{
    // Verificar permisos
    if (!auth()->user()->hasRole('super_admin') && auth()->id() != $user->id) {
        return back()->withErrors(['general' => 'No tienes permiso para actualizar este usuario']);
    }

    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'cedula' => 'required|string|regex:/^[VE]-\d{7,9}$/|unique:users,cedula,' . $user->id,
            'role' => 'nullable|exists:roles,id',
        ]);

        if (auth()->user()->hasRole('super_admin') && isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cedula' => $validated['cedula'],
        ]);

        return redirect()->route('user.index')->with('success', 'Usuario actualizado correctamente');

    } catch (\Exception $e) {
        Log::error('Error actualizando usuario: ' . $e->getMessage());
        return back()->withErrors(['general' => 'Ocurrió un error al guardar los cambios. Por favor, inténtalo de nuevo.']);
    }
}
 public function destroy(User $user)
{
    $user->delete();
    return redirect()->route('user.index')->with('success', 'Usuario eliminado correctamente');
}
}