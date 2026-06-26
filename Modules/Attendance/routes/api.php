<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\DailyEngagementController;
use Modules\Attendance\Http\Controllers\TimeEntryController;

Route::middleware(['auth:sanctum', 'current.company'])
    ->prefix('v1')
    ->group(function () {

        Route::apiResource(
            'time-entries',
            TimeEntryController::class
        )->names('time-entries');

        Route::patch(
            'daily-engagements/{daily_engagement}/exception',
            [DailyEngagementController::class, 'exception']
        );

        Route::post(
            'daily-engagements/{daily_engagement}/submit',
            [DailyEngagementController::class, 'submit']
        );

        Route::post(
            'daily-engagements/{daily_engagement}/approve',
            [DailyEngagementController::class, 'approve']
        );

        Route::post(
            'daily-engagements/{daily_engagement}/reject',
            [DailyEngagementController::class, 'reject']
        );

        Route::apiResource(
            'daily-engagements',
            DailyEngagementController::class
        )->only(['index', 'show'])->names('daily-engagements');
    });
