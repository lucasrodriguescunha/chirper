<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('two_factor.pending_secret');

        $qrSvg = null;
        if ($pendingSecret) {
            $qrSvg = $this->service->qrCodeSvg($user, $pendingSecret);
        }

        return view('settings.two-factor.index', [
            'user' => $user,
            'pendingSecret' => $pendingSecret,
            'qrSvg' => $qrSvg,
            'recoveryCodes' => $request->session()->pull('two_factor.recovery_codes'),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        if ($request->user()->hasTwoFactorEnabled()) {
            return back()->withErrors(['two_factor' => 'Two-factor authentication is already enabled.']);
        }

        $secret = $this->service->generateSecret();
        $request->session()->put('two_factor.pending_secret', $secret);

        return redirect()->route('settings.two-factor.index');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $secret = $request->session()->get('two_factor.pending_secret');

        if (! $secret) {
            return back()->withErrors(['code' => 'No pending two-factor setup. Restart the process.']);
        }

        if (! $this->service->verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Try again.']);
        }

        $codes = $this->service->generateRecoveryCodes();

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($codes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor.pending_secret');
        $request->session()->flash('two_factor.recovery_codes', $codes);

        return redirect()->route('settings.two-factor.index')
            ->with('success', 'Two-factor authentication enabled.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('settings.two-factor.index')
            ->with('success', 'Two-factor authentication disabled.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        if (! $request->user()->hasTwoFactorEnabled()) {
            return back()->withErrors(['recovery' => 'Two-factor authentication is not enabled.']);
        }

        $request->validate([
            'password' => 'required|current_password',
        ]);

        $codes = $this->service->generateRecoveryCodes();
        $request->user()->forceFill([
            'two_factor_recovery_codes' => json_encode($codes),
        ])->save();

        $request->session()->flash('two_factor.recovery_codes', $codes);

        return redirect()->route('settings.two-factor.index')
            ->with('success', 'Recovery codes regenerated.');
    }
}
