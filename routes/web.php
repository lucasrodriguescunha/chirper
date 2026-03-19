<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\Like\LikeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
 * Chirps
 */

Route::get('/', [ChirpController::class, 'index']);

Route::middleware('auth', 'verified')->group(function () {
    Route::resource('chirps', ChirpController::class)
        ->only(['store', 'edit', 'update', 'destroy']);

    Route::post('/chirps/{chirp}/reaction', LikeController::class);
});

/*
 * RegisterController
 */

Route::view('/register', 'auth.register')
    ->middleware('guest')
    ->name('register');

Route::post('/register', RegisterController::class)
    ->middleware('guest');

/*
 * LogoutController
 */

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

/*
 * LoginController
 */

Route::view('/login', 'auth.login')
    ->middleware('guest')
    ->name('login');

Route::post('/login', LoginController::class)
    ->middleware('guest');

/*
 * Email verification
 */

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('')->with('success', 'Email has been verified!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

//Mail::raw('Email verification message', function ($message) {
//    $message->to('test@emai.com')
//        ->subject('Email verification test');
//});







