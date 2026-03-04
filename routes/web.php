<?php

use Illuminate\Support\Facades\Route;
use Intrfce\FFFlags\Http\Controllers\DashboardController;

Route::get(config('ffflags.path', 'ffflags'), DashboardController::class)
    ->middleware(config('ffflags.middleware', ['web', 'auth', 'can:view-ffflags-dashboard']))
    ->name('ffflags.dashboard');
