<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use App\Observers\ModelAuditObserver;
use App\Models\Solicitud;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $observer = ModelAuditObserver::class;

        Solicitud::observe($observer);
        Product::observe($observer);
        Inventory::observe($observer);
        User::observe($observer);
        Inertia::share([
            'auth' => [
                'user' => function () {
                    if (!Auth::check()) {
                        return null;
                    }

                    $user = Auth::user()->loadMissing('gerencia');
                    $roles = $user->getRoleNames()->toArray();
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $roles,
                        'is_admin' => $user->hasRole('admin'),
                        'gerencia' => $user->gerencia ? [
                            'id' => $user->gerencia->id,
                            'name' => $user->gerencia->name,
                        ] : null,
                    ];
                },
            ],

            // Compartir mensajes flash globalmente
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'info' => Session::get('info'),
                    'warning' => Session::get('warning'),
                ];
            },

            // Compartir errores de validación
            'errors' => function () {
                return Session::get('errors')
                    ? Session::get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
        ]);
    }
}
