<?php

use App\Http\Controllers\Settings\BillingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/billing', [BillingController::class, 'index'])
        ->name('settings.billing.index');

    Route::post('/billing/checkout', [BillingController::class, 'checkout'])
        ->name('billing.checkout');

    Route::get('/billing/success', [BillingController::class, 'success'])
        ->name('billing.success');

    Route::get('/billing/cancel', [BillingController::class, 'cancel'])
        ->name('billing.cancel');

    Route::post('/billing/portal', [BillingController::class, 'portal'])
        ->name('billing.portal');

    Route::delete('/billing/subscription', [BillingController::class, 'cancelSubscription'])
        ->name('billing.subscription.cancel');
});
