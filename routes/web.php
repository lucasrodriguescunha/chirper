<?php

use App\Http\Controllers\ChirpController;

Route::get('/', [ChirpController::class, 'index']);

require __DIR__ . '/auth.php';
require __DIR__ . '/password.php';
require __DIR__ . '/profile.php';
require __DIR__ . '/chirps.php';
require __DIR__ . '/verification.php';
require __DIR__ . '/comments.php';
require __DIR__ . '/follows.php';



