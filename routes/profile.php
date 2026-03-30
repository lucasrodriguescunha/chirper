<?php

use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/profile', [ProfileController::class, 'edit'])
        ->name('settings.profile.edit');

    Route::patch('/settings/profile', [ProfileController::class, 'update'])
        ->name('settings.profile.update');
});
