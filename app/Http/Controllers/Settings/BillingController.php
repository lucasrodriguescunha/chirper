<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $subscription = $user->subscription('verified');

        return view('settings.billing.index', [
            'user' => $user,
            'subscription' => $subscription,
            'isVerified' => $user->isVerified(),
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
        ]);
    }

    public function checkout(Request $request)
    {
        $priceId = config('services.stripe.verified_price_id');

        abort_if(blank($priceId), 500, 'Stripe price ID not configured.');

        return $request->user()
            ->newSubscription('verified', $priceId)
            ->checkout([
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.cancel'),
            ]);
    }

    public function success()
    {
        return redirect()->route('settings.billing.index')
            ->with('success', 'Subscription activated. Your verified badge will appear shortly.');
    }

    public function cancel()
    {
        return redirect()->route('settings.billing.index')
            ->with('error', 'Checkout canceled.');
    }

    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('settings.billing.index'));
    }

    public function cancelSubscription(Request $request)
    {
        $subscription = $request->user()->subscription('verified');

        abort_if(! $subscription || $subscription->canceled(), 404);

        $subscription->cancel();

        return redirect()->route('settings.billing.index')
            ->with('success', 'Subscription will end at the end of the current billing period.');
    }
}
