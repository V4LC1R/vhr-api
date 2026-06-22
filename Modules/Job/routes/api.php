<?php

use Illuminate\Support\Facades\Route;
use Modules\Job\Http\Controllers\EmployeeController;

Route::middleware(['auth:sanctum','current.company'])
    ->prefix('v1')
    ->group(function () {

        Route::patch(
            'employees/{employee}/dismiss',
            [EmployeeController::class, 'dismiss']
        )->middleware('can:dismiss,employee');

        Route::apiResource(
            'employees',
            EmployeeController::class
        )->names('employees');
    });
