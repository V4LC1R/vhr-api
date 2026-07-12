<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard/employees')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Employees/List')->name('employees.list');
        Route::inertia('/create', 'Dashboard/Employees/New')->name('employees.create');
        Route::get('/{id}/edit', fn (string $id) => Inertia::render('Dashboard/Employees/Edit', ['id' => $id]))
            ->name('employees.edit');
    });

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard/workloads')
    ->group(function () {
        Route::inertia('/', 'Dashboard/Workloads/List')->name('workloads.list');
    });
