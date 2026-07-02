<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Auth/Login')
    ->middleware();

Route::inertia('/forgot-password', 'Auth/ForgotPassword')
    ->middleware();

Route::get('/reset-password/{token}', fn (string $token) => Inertia::render('Auth/ResetPassword', [
    'token' => $token,
]))->name('password.reset');
