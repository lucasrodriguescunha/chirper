<?php

use App\Http\Controllers\BookmarkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/chirps/{chirp}/bookmark', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::delete('/chirps/{chirp}/bookmark', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
});
