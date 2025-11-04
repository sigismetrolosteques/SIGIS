<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Gerencia;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Services\RoleService;
use App\Mail\WelcomeUserMail;

class UserController extends Controller
{
    protected array $adminGerencias = [
        'Administración',
        'Normalización',
        'VP. Mantenimiento',
        'VP. Soporte',
        'Compras',
    ];

    public function index()
    {
        $authUser = Auth::user();

        $isSuperAdmin = $authUser->hasRole('super_admin');
        $isAdmin = $authUser->hasRole('admin');

        if ($isSuperAdmin) {
            $users = User::with(['roles:id,name', 'gerencia:id,name'])
                ->latest()
                ->paginate(10);
        } elseif ($isAdmin) {
            $users = User::with(['roles:id,name', 'gerencia:id,name'])
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))
                ->latest()
                ->paginate(10);
        } else {
            $users = User::with(['roles:id,name', 'gerencia:id,name'])
                ->where('id', $authUser->id)
                ->paginate(1);
        }

        return Inertia::render('User/Index', [
            'users' => $users,
            'can' => [
                'create' => $isSuperAdmin || $isAdmin,
                'edit' => $isSuperAdmin || $isAdmin,
                'delete' => $isSuperAdmin || $isAdmin,
            ],
            'is_super_admin' => $isSuperAdmin
        ]);
    }

    public function create()
    {
        $this->authorizeUserManagement();

        return Inertia::render('User/Create', [
            'gerencias' => Gerencia::all(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeUserManagement();

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.regex' => 'La cédula debe tener el formato V-12345678.',
            'cedula.unique' => 'La cédula ya está registrada.',
            'gerencia_id.required' => 'Selecciona una gerencia.',
            'gerencia_id.exists' => 'La gerencia seleccionada no es válida.',
        ];

        $attributes = [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'cedula' => 'cédula',
            'gerencia_id' => 'gerencia',
        ];

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'cedula'      => 'required|string|regex:/^[VE]-\d{7,9}$/|unique:users',
            'gerencia_id' => 'required|exists:gerencias,id',
        ], $messages, $attributes);

        $gerencia = Gerencia::findOrFail($request->gerencia_id);
        $tempPassword = Str::random(8);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'cedula'      => $request->cedula,
            'gerencia_id' => $request->gerencia_id,
            'password'    => Hash::make($tempPassword),
            'is_first_login' => true,
            'security_question_1' => null,
            'security_answer_1'   => null,
            'security_question_2' => null,
            'security_answer_2'   => null,
            'security_question_3' => null,
            'security_answer_3'   => null,
        ]);

        $user->assignRole($this->resolveRoleForGerencia($gerencia->name));

        // Enviar correo de bienvenida
        try {
            Mail::to($user->email)->send(new WelcomeUserMail(
                $user->name,
                $user->email,
                $tempPassword
            ));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de bienvenida: ' . $e->getMessage());
        }

        return redirect()->route('user.index')->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $user)
    {
        $auth = auth()->user();

        if ($user->hasRole('super_admin') && !$auth->hasRole('super_admin')) {
            abort(403, 'No tienes permiso para editar este usuario');
        }

        if (!$auth->hasAnyRole(['super_admin', 'admin']) && $auth->id != $user->id) {
            abort(403, 'No tienes permiso para editar este usuario');
        }

        return Inertia::render('User/Edit', [
            'user' => $user->load('roles:id,name', 'gerencia:id,name'),
            'roles' => auth()->user()->hasRole('super_admin')
                ? app(RoleService::class)->getRoles()
                : null,
            'gerencias' => Gerencia::all(),
            'canEditRole' => auth()->user()->hasRole('super_admin'),
            'canManageGerencia' => auth()->user()->hasAnyRole(['super_admin', 'admin']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $auth = auth()->user();

        if ($user->hasRole('super_admin') && !$auth->hasRole('super_admin')) {
            return back()->withErrors(['general' => 'No tienes permiso para actualizar este usuario']);
        }

        if (!$auth->hasAnyRole(['super_admin', 'admin']) && $auth->id != $user->id) {
            return back()->withErrors(['general' => 'No tienes permiso para actualizar este usuario']);
        }

        try {
            $isManager = $auth->hasAnyRole(['super_admin', 'admin']);

            $messages = [
                'name.required' => 'El nombre es obligatorio.',
                'name.max' => 'El nombre no puede superar 255 caracteres.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Ingresa un correo electrónico válido.',
                'email.unique' => 'El correo electrónico ya está registrado.',
                'cedula.required' => 'La cédula es obligatoria.',
                'cedula.regex' => 'La cédula debe tener el formato V-12345678.',
                'cedula.unique' => 'La cédula ya está registrada.',
                'gerencia_id.required' => 'Selecciona una gerencia.',
                'gerencia_id.exists' => 'La gerencia seleccionada no es válida.',
            ];

            $attributes = [
                'name' => 'nombre',
                'email' => 'correo electrónico',
                'cedula' => 'cédula',
                'gerencia_id' => 'gerencia',
            ];

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|email|unique:users,email,' . $user->id,
                'cedula'      => 'required|string|regex:/^[VE]-\d{7,9}$/|unique:users,cedula,' . $user->id,
                'gerencia_id' => $isManager ? 'required|exists:gerencias,id' : 'nullable',
            ], $messages, $attributes);

            $gerenciaId = $isManager ? $validated['gerencia_id'] : $user->gerencia_id;
            $gerencia = Gerencia::findOrFail($gerenciaId);

            $user->update([
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'cedula'      => $validated['cedula'],
                'gerencia_id' => $gerenciaId,
            ]);

            if (!$user->hasRole('super_admin')) {
                $user->syncRoles([$this->resolveRoleForGerencia($gerencia->name)]);
            }

            return redirect()->route('user.index')->with('success', 'Usuario actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error actualizando usuario: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Ocurrió un error al guardar los cambios. Por favor, inténtalo de nuevo.']);
        }
    }

    public function destroy(User $user)
    {
        $auth = auth()->user();

        if (!$auth->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar usuarios');
        }

        if ($user->hasRole('super_admin')) {
            abort(403, 'No es posible eliminar al Super Administrador');
        }

        if ($auth->id === $user->id) {
            abort(403, 'No puedes eliminar tu propio usuario');
        }

        try {
            DB::transaction(function () use ($user, $auth) {
                Solicitud::where('created_by', $user->id)->update([
                    'created_by' => $auth->id,
                ]);

                $user->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Error eliminando usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo eliminar el usuario. Por favor, intenta nuevamente.']);
        }

        return redirect()->route('user.index')->with('success', 'Usuario eliminado correctamente');
    }

    protected function resolveRoleForGerencia(string $gerenciaName): string
    {
        return in_array($gerenciaName, $this->adminGerencias, true) ? 'admin' : 'viewer';
    }

    protected function authorizeUserManagement(): void
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'No tienes permiso para gestionar usuarios');
        }
    }
}
