<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private TwoFactorService $service) {}

    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $user = User::find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor.user_id', 'two_factor.remember']);

            return redirect()->route('login');
        }

        $code = trim((string) $request->input('code', ''));
        $recovery = trim((string) $request->input('recovery_code', ''));

        $authenticated = false;

        if ($code !== '' && $this->service->verify($user->two_factor_secret, $code)) {
            $authenticated = true;
        } elseif ($recovery !== '' && $this->service->consumeRecoveryCode($user, $recovery)) {
            $authenticated = true;
        }

        if (! $authenticated) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }

        $remember = (bool) $request->session()->pull('two_factor.remember', false);
        $request->session()->forget('two_factor.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', 'Welcome back!');
    }
}
