<?php

namespace App\Listeners;

use App\Models\User;
use Laravel\Cashier\Events\WebhookReceived;

class HandleVerifiedSubscription
{
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;
        $object = $payload['data']['object'] ?? [];

        $customerId = $object['customer'] ?? null;

        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_id', $customerId)->first();

        if (! $user) {
            return;
        }

        match ($type) {
            'invoice.payment_succeeded' => $this->markVerified($user),
            'customer.subscription.deleted',
            'invoice.payment_failed' => $this->unmarkVerified($user),
            default => null,
        };
    }

    private function markVerified(User $user): void
    {
        if (! $user->isVerified()) {
            $user->forceFill(['verified_at' => now()])->save();
        }
    }

    private function unmarkVerified(User $user): void
    {
        if ($user->isVerified()) {
            $user->forceFill(['verified_at' => null])->save();
        }
    }
}
