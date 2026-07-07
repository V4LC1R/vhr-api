<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| API Routes - Módulo Auth
|--------------------------------------------------------------------------
*/

// Rotas Públicas (Visitantes)
Route::middleware(['web'])->post('auth/login', [AuthController::class, 'login'])->name('auth.login');

// Recuperação de senha (público, com throttle)
Route::middleware(['web', 'throttle:6,1'])->prefix('auth')->group(function () {
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->name('auth.password.forgot');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('auth.password.reset');
});

// Rotas Protegidas (Apenas usuários autenticados via cookie/sessão)
Route::middleware(['web','auth:sanctum'])->prefix('auth')->group(function () {
    Route::post('/select-company', [AuthController::class, 'selectCompany'])->name('auth.select.company');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
