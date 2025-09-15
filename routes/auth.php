<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SecurityAnswerController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\FirstLoginController;
use Illuminate\Support\Facades\Route;

// Rutas para invitados
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    // Ruta de primer login (accesible sin autenticación)
Route::get('/first-login', [FirstLoginController::class, 'create'])
     ->name('first.login');
     Route::post('/first-login', [FirstLoginController::class, 'store'])
     ->name('first.login.store');


    // Recuperación de contraseña personalizada
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');
                
    Route::post('forgot-password/verify-security', [SecurityAnswerController::class, 'verify'])
                ->name('password.verify.security');
    
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');
    
    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

// Rutas para autenticados
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
                
});

