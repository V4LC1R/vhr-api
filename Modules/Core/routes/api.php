<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\PersonController;
use Modules\Core\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('persons', PersonController::class)->names('core');
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class)->names('core');
});
