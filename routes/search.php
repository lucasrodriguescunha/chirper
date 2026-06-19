<?php

use App\Http\Controllers\SearchController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/search', [SearchController::class, 'index'])->name('search');
});
