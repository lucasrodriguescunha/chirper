<?php

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Settings\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('two-factor.challenge');

    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:6,1');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/two-factor', [TwoFactorController::class, 'index'])
        ->name('settings.two-factor.index');

    Route::post('/settings/two-factor/enable', [TwoFactorController::class, 'enable'])
        ->name('settings.two-factor.enable');

    Route::post('/settings/two-factor/confirm', [TwoFactorController::class, 'confirm'])
        ->name('settings.two-factor.confirm');

    Route::delete('/settings/two-factor', [TwoFactorController::class, 'disable'])
        ->name('settings.two-factor.disable');

    Route::post('/settings/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
        ->name('settings.two-factor.recovery-codes');
});
