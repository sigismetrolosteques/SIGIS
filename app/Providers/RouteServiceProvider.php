<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/solicitudes'; // Ruta por defecto
    public const ADMIN_HOME = '/solicitudes'; // Ruta para admin
    public const SUPER_ADMIN_HOME = '/solicitudes'; // Ruta exclusiva para super admin

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        Route::bind('user', function ($value) {
            return User::with('roles')->findOrFail($value);
        });

        // Configuración de redirección post-login mejorada
        $this->app->bind(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            function ($app) {
                $auth = $app->make(AuthFactory::class);
                $user = $auth->guard()->user();

                if (!$user) {
                    return new \Illuminate\Http\RedirectResponse(url(self::HOME));
                }

                $redirectTo = match(true) {
                    $user->hasRole('super_admin') => self::SUPER_ADMIN_HOME,
                    $user->hasRole('admin') => self::ADMIN_HOME,
                    default => self::HOME
                };

                return new \Illuminate\Http\RedirectResponse(url($redirectTo));
            }
        );
    }
}
