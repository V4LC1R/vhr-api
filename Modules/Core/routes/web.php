<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Home')->name('home');
    });

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard/companies')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Companies/List')->name('companies.list');
    });

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard/users')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Users/List')->name('users.list');
    });
