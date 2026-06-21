<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\Like\CommentLikeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/chirps/{chirp}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('comments.store');

    Route::put('/chirps/{chirp}/comments/{comment}', [CommentController::class, 'update'])
        ->name('comments.update');

    Route::delete('/chirps/{chirp}/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::post('/comments/{comment}/reaction', CommentLikeController::class)
        ->middleware('throttle:60,1');
});
