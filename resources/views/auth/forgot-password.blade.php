<x-layout>
    <x-slot:title>
        Recover password
    </x-slot:title>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col">
            <div class="card w-96 bg-base-100">
                <div class="card-body text-center">

                    <h1 class="text-xl font-bold mb-2">
                        Forgot your password?
                    </h1>

                    <p class="text-base-content/60 text-sm mb-6">
                        Enter your email and we'll send you a reset link.
                    </p>

                    @if (session('status'))
                        <x-alert type="success" size="sm" soft class="mb-4 text-left">{{ session('status') }}</x-alert>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <label class="floating-label mb-4">
                            <input type="email"
                                   name="email"
                                   placeholder="mail@example.com"
                                   value="{{ old('email') }}"
                                   class="input input-bordered @error('email') input-error @enderror"
                                   required
                                   autofocus>
                            <span>Email</span>
                        </label>

                        @error('email')
                            <x-alert type="error" size="sm" soft class="mb-4 text-left">{{ $message }}</x-alert>
                        @enderror

                        <button class="btn btn-primary w-full">
                            Send reset link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
