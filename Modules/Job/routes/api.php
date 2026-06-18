<?php

use Illuminate\Support\Facades\Route;
use Modules\Job\Http\Controllers\JobController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('jobs', JobController::class)->names('job');
});
