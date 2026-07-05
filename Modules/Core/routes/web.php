<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CoreController;
use Inertia\Inertia;

Route::prefix('dashboard')->group(function () {
    Route::inertia('/', 'Dashboard/Home')->name('home');
});
