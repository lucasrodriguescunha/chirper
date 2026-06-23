<?php

use App\Http\Controllers\ChirpController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [ChirpController::class, 'index']);
});

require __DIR__ . '/auth.php';
require __DIR__ . '/password.php';
require __DIR__ . '/profile.php';
require __DIR__ . '/chirps.php';
require __DIR__ . '/verification.php';
require __DIR__ . '/comments.php';
require __DIR__ . '/follows.php';
require __DIR__ . '/users.php';
require __DIR__ . '/search.php';
require __DIR__ . '/notifications.php';
require __DIR__ . '/two_factor.php';
require __DIR__ . '/bookmarks.php';
require __DIR__ . '/tags.php';



