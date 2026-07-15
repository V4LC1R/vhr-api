<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'current.company', 'no-store'])
    ->prefix('dashboard')
    ->group(function () {
        Route::inertia('/time-entries', 'Dashboard/TimeEntries/List')->name('time-entries.list');
        Route::inertia('/approvals', 'Dashboard/Approvals/List')->name('approvals.list');
        Route::inertia('/reports/hours-summary', 'Dashboard/Reports/HoursSummary')->name('reports.hoursSummary');
        Route::inertia('/reports/absences', 'Dashboard/Reports/Absences')->name('reports.absences');
        Route::inertia('/reports/dayli-workers', 'Dashboard/Reports/DayliWorkers')->name('reports.dayliWorkers');
    });
