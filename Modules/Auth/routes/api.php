<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - Módulo Auth
|--------------------------------------------------------------------------
*/

// Rotas Públicas (Visitantes)
Route::middleware(['web'])->post('auth/login', [AuthController::class, 'login'])->name('auth.login');

// Rotas Protegidas (Apenas usuários autenticados via cookie/sessão)
Route::middleware(['web','auth:sanctum'])->prefix('auth')->group(function () {

    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
