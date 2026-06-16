<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CompanyController;
use Modules\Core\Http\Controllers\PersonController;
use Modules\Core\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::apiResource('persons', PersonController::class)->names([
        'index'   => 'core.persons.index',
        'store'   => 'core.persons.store',
        'show'    => 'core.persons.show',
        'update'  => 'core.persons.update',
        'destroy' => 'core.persons.destroy',
    ]);

    Route::apiResource('users', UserController::class)->names([
        'index'   => 'core.users.index',
        'store'   => 'core.users.store',
        'show'    => 'core.users.show',
        'update'  => 'core.users.update',
        'destroy' => 'core.users.destroy',
    ]);

    // Alterado para plural 'companies' e mapeado explicitamente para casar com o teste
    Route::apiResource('companies', CompanyController::class)->names([
        'index'   => 'core.companies.index',
        'store'   => 'core.companies.store',
        'show'    => 'core.companies.show',
        'update'  => 'core.companies.update',
        'destroy' => 'core.companies.destroy',
    ]);
});
