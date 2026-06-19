<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\Like\CommentLikeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/chirps/{chirp}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    Route::delete('/chirps/{chirp}/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::post('/comments/{comment}/reaction', CommentLikeController::class);
});
