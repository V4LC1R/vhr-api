<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/welcome', fn () => Inertia::render('Welcome', [
    'appName' => config('app.name'),
]));
