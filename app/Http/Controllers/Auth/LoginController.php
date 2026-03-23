<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->clearAttempts();
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Welcome back!');
        }

        $request->hitAttempt();

        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email');
    }
}
