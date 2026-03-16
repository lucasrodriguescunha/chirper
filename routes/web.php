<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\ChirpController;
use Illuminate\Http\Request;

/*
 * Chirps
 */

Route::get('/', [ChirpController::class, 'index']);

Route::middleware('auth', 'verified')->group(function () {
    Route::resource('chirps', ChirpController::class)
        ->only(['store', 'edit', 'update', 'destroy']);
});


/*
 * Register
 */

Route::view('/register', 'auth.register')
    ->middleware('guest')
    ->name('register');

Route::post('/register', Register::class)
    ->middleware('guest');

/*
 * Logout
 */

Route::post('/logout', Logout::class)
    ->middleware('auth')
    ->name('logout');

/*
 * Login
 */

Route::view('/login', 'auth.login')
    ->middleware('guest')
    ->name('login');

Route::post('/login', Login::class)
    ->middleware('guest');

/*
 * Email verification
 */

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fullfill();

    return redirect('')->with('success', 'Email has been verified!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');




