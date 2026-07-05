<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Home')->name('home');
    });
