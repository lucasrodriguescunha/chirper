<?php

use App\Http\Controllers\ChirpController;
use App\Http\Controllers\Like\LikeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('chirps', ChirpController::class)
        ->only(['edit', 'update', 'destroy']);

    Route::post('/chirps', [ChirpController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('chirps.store');

    Route::post('/chirps/{chirp}/reaction', LikeController::class)
        ->middleware('throttle:60,1');
});
