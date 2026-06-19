<?php

use App\Http\Controllers\FollowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])
        ->name('follows.store');

    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])
        ->name('follows.destroy');
});
