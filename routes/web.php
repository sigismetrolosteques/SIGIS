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
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AlertController;

// Rutas públicas
Route::middleware('web')->get('/csrf/refresh', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

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
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::get('/product/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
    Route::post('/product/{product}/update', [ProductController::class, 'update'])->name('product.update');
    Route::post('/product/{product}/delete', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/product/{product}/pdf', [ProductController::class, 'exportPdf'])->name('product.pdf');
    Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::post('/inventory/{inventory}/update', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/{inventory}/delete', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventory/{inventory}', [App\Http\Controllers\InventoryController::class, 'show'])
        ->name('inventory.show');


    Route::get('/solicitudes', [RequestController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/create', [RequestController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [RequestController::class, 'store'])->name('solicitudes.store');

    Route::get('/solicitudes/{solicitud}/pdf', [RequestController::class, 'generatePDF'])
        ->name('solicitudes.pdf');
    Route::get('/solicitudes/{solicitud}/adjunto/{log}', [RequestController::class, 'viewAttachment'])
        ->name('solicitudes.attachment');

    // Botones aprobar/rechazar
    Route::post('/solicitudes/{solicitud}/update-status', [RequestController::class, 'updateStatus'])
        ->name('solicitudes.updateStatus');
    Route::get('/reportes/solicitudes', [ReportController::class, 'index'])->name('reportes.solicitudes');
    Route::get('/reportes/solicitudes/pdf', [ReportController::class, 'exportPDF'])->name('reportes.solicitudes.pdf');
    Route::get('/reportes/inventario', [ReportController::class, 'inventory'])->name('reportes.inventario');
    Route::get('/reportes/inventario/pdf', [ReportController::class, 'inventoryPdf'])->name('reportes.inventario.pdf');
    Route::get('/reportes/productos', [ReportController::class, 'products'])->name('reportes.productos');
    Route::get('/reportes/productos/pdf', [ReportController::class, 'productsPdf'])->name('reportes.productos.pdf');
    Route::get('/alertas', [AlertController::class, 'index'])->name('alertas.index');
    Route::get('/alertas/resumen', [AlertController::class, 'summary'])->name('alertas.summary');
    Route::get('/auditoria', [AuditController::class, 'index'])->name('auditoria.index');
    Route::get('/auditoria/export', [AuditController::class, 'exportCsv'])->name('auditoria.export');


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
