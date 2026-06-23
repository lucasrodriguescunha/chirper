<?php

use App\Http\Controllers\TagController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');
});
