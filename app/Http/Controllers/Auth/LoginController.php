<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected int $maxAttempts = 3;
    protected int $decaySeconds = 900;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Validate the input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Check if it's blocked.
        $this->checkTooManyAttempts($request);

        // Attempt to log in
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clears login attempts after successful login
            RateLimiter::clear($this->throttleKey($request));

            // Regenerate session for security
            $request->session()->regenerate();

            // Redirect to intended page or home
            return redirect()->intended('/')->with('success', 'Welcome back!');
        }

        // Records the failed attempt
        RateLimiter::hit($this->throttleKey($request), $this->decaySeconds);

        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email');
    }

    protected function checkTooManyAttempts(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Please try again in {$this->formatWaitTime($seconds)}.",
        ]);
    }

    protected function formatWaitTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "$seconds second" . ($seconds !== 1 ? 's' : '');
        }

        if ($seconds < 3600) {
            $minutes = (int) ceil($seconds / 60);
            return "$minutes minute" . ($minutes !== 1 ? 's' : '');
        }

        $hours = (int) ceil($seconds / 3600);
        return "$hours hour" . ($hours !== 1 ? 's' : '');
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email'))) . '|' . $request->ip();
    }
}
