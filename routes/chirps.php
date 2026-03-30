<?php

use App\Http\Controllers\ChirpController;
use App\Http\Controllers\Like\LikeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('chirps', ChirpController::class)
        ->only(['store', 'edit', 'update', 'destroy']);

    Route::post('/chirps/{chirp}/reaction', LikeController::class);
});
