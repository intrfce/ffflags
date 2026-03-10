<?php

use Illuminate\Support\Facades\Route;
use Intrfce\FFFlags\Http\Controllers\Api\FeatureDetailController;
use Intrfce\FFFlags\Http\Controllers\Api\FeatureListController;

$middleware = config('ffflags.middleware', ['web', 'auth', 'can:view-ffflags-dashboard']);
$path = config('ffflags.path', 'ffflags');

Route::prefix($path.'-api')->middleware($middleware)->group(function () {
    Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('ffflags.api.csrf');
    Route::get('/features', FeatureListController::class)->name('ffflags.api.features.index');
    Route::get('/features/{slug}', [FeatureDetailController::class, 'show'])->name('ffflags.api.features.show');
    Route::post('/features/{slug}', [FeatureDetailController::class, 'update'])->name('ffflags.api.features.update');
    Route::post('/features/{slug}/check', [FeatureDetailController::class, 'check'])->name('ffflags.api.features.check');
});
