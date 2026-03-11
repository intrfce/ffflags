<?php

use Illuminate\Support\Facades\Route;
use Intrfce\FFFlags\Http\Controllers\DashboardController;
use Intrfce\FFFlags\Http\Controllers\FeatureDetailController;

$middleware = config('ffflags.middleware', ['web', 'auth', 'can:view-ffflags-dashboard']);
$path = config('ffflags.path', 'ffflags');

Route::get($path, DashboardController::class)
    ->middleware($middleware)
    ->name('ffflags.dashboard');

Route::get($path . '/features/{slug}', [FeatureDetailController::class, 'show'])
    ->middleware($middleware)
    ->name('ffflags.feature.show');

Route::post($path . '/features/{slug}', [FeatureDetailController::class, 'update'])
    ->middleware($middleware)
    ->name('ffflags.feature.update');

Route::post($path . '/features/{slug}/check', [FeatureDetailController::class, 'check'])
    ->middleware($middleware)
    ->name('ffflags.feature.check');
