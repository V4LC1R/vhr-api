<?php

use Illuminate\Support\Facades\Route;
use Modules\Job\Http\Controllers\EmployeeController;
use Modules\Job\Http\Controllers\WorkloadController;

Route::middleware(['auth:sanctum','current.company'])
    ->prefix('v1')
    ->group(function () {

        Route::patch(
            'employees/{employee}/dismiss',
            [EmployeeController::class, 'dismiss']
        )->middleware('can:dismiss,employee');

        Route::get(
            'employees/next-register-number',
            [EmployeeController::class, 'nextRegisterNumber']
        )->name('employees.nextRegisterNumber');

        Route::apiResource(
            'employees',
            EmployeeController::class
        )->names('employees');

        Route::apiResource(
            'workloads',
            WorkloadController::class
        )->names('workloads');
    });
