<x-layout>
    <x-slot:title>Two-Factor Challenge</x-slot:title>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col">
            <div class="card w-96 bg-base-100">
                <div class="card-body">
                    <h1 class="text-xl mt-1 font-bold text-center mb-2">Two-Factor Authentication</h1>
                    <p class="text-sm text-center text-base-content/70 mb-6">
                        Enter the 6-digit code from your authenticator app, or use a recovery code.
                    </p>

                    @error('code')
                        <x-alert type="error" size="sm" soft class="mb-4">{{ $message }}</x-alert>
                    @enderror

                    <form method="POST" action="{{ route('two-factor.challenge') }}">
                        @csrf

                        <label class="floating-label mb-4">
                            <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                                   placeholder="123456"
                                   class="input input-bordered @error('code') input-error @enderror"
                                   autofocus>
                            <span>Authenticator code</span>
                        </label>

                        <div class="divider text-xs">OR</div>

                        <label class="floating-label mb-4">
                            <input type="text" name="recovery_code" placeholder="xxxxx-xxxxx"
                                   class="input input-bordered">
                            <span>Recovery code</span>
                        </label>

                        <div class="form-control mt-4">
                            <button type="submit" class="btn btn-primary w-full">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
