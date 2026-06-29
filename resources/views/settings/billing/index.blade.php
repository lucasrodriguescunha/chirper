<x-layout>
    <x-slot:title>
        Billing
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl sm:text-3xl font-bold mt-6 sm:mt-8">Billing</h1>
        <p class="text-base-content/70 mt-2">Get the <strong>Verified Badge</strong> for R$ 5.00 / month.</p>

        <div class="card bg-base-100 mt-6 sm:mt-8">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-semibold">Status:</span>
                    @if ($isVerified)
                        <span class="badge badge-info gap-1">
                            <x-verified-badge :show="true" class="w-3.5 h-3.5" />
                            Verified
                        </span>
                    @else
                        <span class="badge badge-ghost">Not verified</span>
                    @endif
                </div>

                @if ($subscription)
                    <div class="mt-2 text-sm text-base-content/70">
                        @if ($onGracePeriod)
                            Subscription canceled. Access remains until <strong>{{ optional($subscription->ends_at)->format('M d, Y') }}</strong>.
                        @elseif ($subscription->active())
                            Subscription <strong>active</strong>. You can manage your billing or cancel below.
                        @elseif ($subscription->canceled())
                            Subscription ended.
                        @endif
                    </div>
                @endif

                <div class="divider"></div>

                @if (! $subscription || (! $subscription->active() && ! $onGracePeriod))
                    <form method="POST" action="{{ route('billing.checkout') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Get verified — R$ 5.00 / month
                        </button>
                    </form>
                @else
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('billing.portal') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline">
                                Manage billing
                            </button>
                        </form>

                        @if ($subscription->active() && ! $onGracePeriod)
                            <form method="POST" action="{{ route('billing.subscription.cancel') }}"
                                  onsubmit="return confirm('Cancel subscription? Verified badge stays until the end of the current billing period.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost text-error">
                                    Cancel subscription
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @error('checkout')
                    <x-alert type="error" class="mt-4">{{ $message }}</x-alert>
                @enderror
            </div>
        </div>

        <div class="mt-6 text-sm text-base-content/60">
            <p>Test card: <code>4242 4242 4242 4242</code> — any future date, any CVC.</p>
        </div>
    </div>
</x-layout>
