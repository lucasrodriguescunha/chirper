<?php

use App\Http\Controllers\NotificationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
