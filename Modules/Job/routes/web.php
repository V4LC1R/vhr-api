<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard/employees')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Employees/List')->name('employees.list');
        Route::inertia('/create', 'Dashboard/Employees/New')->name('employees.create');
        Route::inertia('/{id}/edit', 'Dashboard/Employees/Edit')->name('employees.edit');
    });
