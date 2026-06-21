<x-layout>
    <x-slot:title>Two-Factor Authentication</x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl sm:text-3xl font-bold mt-6 sm:mt-8">Two-Factor Authentication</h1>
        <p class="text-base-content/70 mt-2">Add an extra layer of security to your account using a TOTP app (Google Authenticator, 1Password, Authy).</p>

        @if ($errors->any())
            <div class="alert alert-error mt-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card bg-base-100 mt-6">
            <div class="card-body">
                @if ($user->hasTwoFactorEnabled())
                    <div class="badge badge-success">Enabled</div>
                    <p class="mt-2">Two-factor authentication is active on your account.</p>

                    @if ($recoveryCodes)
                        <div class="alert alert-warning mt-4">
                            <div>
                                <p class="font-semibold">Save these recovery codes</p>
                                <p class="text-sm">Each can be used once if you lose access to your authenticator app.</p>
                                <ul class="mt-2 font-mono text-sm grid grid-cols-2 gap-1">
                                    @foreach ($recoveryCodes as $code)
                                        <li>{{ $code }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.two-factor.recovery-codes') }}" class="mt-6 space-y-3">
                        @csrf
                        <h3 class="font-semibold">Regenerate recovery codes</h3>
                        <input type="password" name="password" placeholder="Current password"
                               class="input input-bordered w-full" required>
                        <button type="submit" class="btn btn-outline">Regenerate</button>
                    </form>

                    <form method="POST" action="{{ route('settings.two-factor.disable') }}" class="mt-6 space-y-3">
                        @csrf
                        @method('DELETE')
                        <h3 class="font-semibold text-error">Disable 2FA</h3>
                        <input type="password" name="password" placeholder="Current password"
                               class="input input-bordered w-full" required>
                        <button type="submit" class="btn btn-error">Disable</button>
                    </form>
                @elseif ($pendingSecret)
                    <div class="badge badge-warning">Pending confirmation</div>
                    <p class="mt-2">Scan this QR code with your authenticator app, then enter the 6-digit code below.</p>

                    <div class="flex justify-center mt-4 p-4 bg-white rounded">
                        {!! $qrSvg !!}
                    </div>

                    <p class="text-sm mt-3">Or enter this secret manually:</p>
                    <code class="block p-2 bg-base-200 rounded text-sm font-mono break-all">{{ $pendingSecret }}</code>

                    <form method="POST" action="{{ route('settings.two-factor.confirm') }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                               placeholder="6-digit code"
                               class="input input-bordered w-full" required autofocus>
                        <button type="submit" class="btn btn-primary w-full">Confirm and enable</button>
                    </form>
                @else
                    <div class="badge badge-ghost">Disabled</div>
                    <p class="mt-2">Two-factor authentication is not yet enabled.</p>
                    <form method="POST" action="{{ route('settings.two-factor.enable') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary">Enable 2FA</button>
                    </form>
                @endif
            </div>
        </div>

        <a href="{{ route('settings.profile.edit') }}" class="btn btn-ghost mt-4">Back to profile</a>
    </div>
</x-layout>
