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
                        <div class="alert alert-success mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <label class="floating-label mb-6">
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
                        <div class="label -mt-4 mb-2">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                        @enderror

                        <button class="btn btn-primary btn-sm w-full">
                            Send reset link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
