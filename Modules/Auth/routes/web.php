<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('auth')->group(function () {
    Route::inertia('/login', 'Auth/Login')->name('login');

    Route::inertia('/forgot-password', 'Auth/ForgotPassword')->name('password.request');

    Route::get('/reset-password/{token}', fn (string $token) => Inertia::render('Auth/ResetPassword', [
        'token' => $token,
    ]))->name('password.reset');
});
