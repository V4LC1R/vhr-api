<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\DailyEngagementController;
use Modules\Attendance\Http\Controllers\TimeEntryController;

Route::middleware(['auth:sanctum', 'current.company'])
    ->prefix('v1')
    ->group(function () {

        Route::post(
            'time-entries/batch',
            [TimeEntryController::class, 'storeBatch']
        );

        Route::apiResource(
            'time-entries',
            TimeEntryController::class
        )->names('time-entries');

        Route::post(
            'daily-engagements/approve-batch',
            [DailyEngagementController::class, 'approveBatch']
        );

        Route::post(
            'daily-engagements/reject-batch',
            [DailyEngagementController::class, 'rejectBatch']
        );

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
        )->only(['index', 'show', 'store'])->names('daily-engagements');
    });
