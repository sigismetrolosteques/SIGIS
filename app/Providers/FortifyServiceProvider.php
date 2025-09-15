<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
{
    
}

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    Fortify::authenticateUsing(function (Request $request) {
        // Tu lógica personalizada aquí si es necesario
    });

    Fortify::loginView(function () {
        return Inertia::render('Auth/Login');
    });

    // Sobrescribe el manejo de login
    $this->app->singleton(
        \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class,
        \App\Http\Controllers\Auth\CustomAuthenticatedSessionController::class
    );
}
}