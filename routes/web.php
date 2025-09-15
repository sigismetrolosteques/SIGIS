<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\FirstLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Rutas públicas
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rutas de autenticación personalizadas
require __DIR__ . '/auth.php';

// Rutas autenticadas comunes
Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta para gestión de usuarios
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/user/{user}/update', [UserController::class, 'update'])->name('user.update');
    
    // Rutas solo para super_admin
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::post('/user/{user}/delete', [UserController::class, 'destroy'])->name('user.destroy');

    // Rutas de perfil separadas
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/security-questions', [ProfileController::class, 'updateSecurityQuestions'])->name('profile.security-questions.update');
});

// Rutas para cambio/reseteo de contraseña (sin autenticación)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/forgot-password/email', [ForgotPasswordController::class, 'handleEmail'])
        ->name('password.email');
    Route::get('/forgot-password/questions', [ForgotPasswordController::class, 'showSecurityQuestions'])
        ->name('password.questions');
    Route::post('/forgot-password/verify-security', [ForgotPasswordController::class, 'verifySecurityAnswers'])
        ->name('password.verify.security');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});