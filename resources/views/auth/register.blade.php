<x-layout>
    <x-slot:title>
        Register
    </x-slot:title>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col w-full px-4 sm:px-0">
            <div class="card w-full max-w-sm bg-base-100">
                <div class="card-body">
                    <h1 class="text-xl mt-1 font-bold text-center mb-6">Create Account</h1>

                    <form method="POST" action="/register">
                        @csrf

                        <label class="floating-label mb-4">
                            <input
                                type="text"
                                name="name"
                                placeholder="John Doe"
                                value="{{ old('name') }}"
                                class="input input-bordered @error('name') input-error @enderror"
                                required
                            >
                            <span>Name</span>
                        </label>

                        @error('name')
                            <x-alert type="error" size="sm" soft class="mb-4">{{ $message }}</x-alert>
                        @enderror

                        <label class="floating-label mb-4">
                            <input
                                type="email"
                                name="email"
                                placeholder="mail@example.com"
                                value="{{ old('email') }}"
                                class="input input-bordered @error('email') input-error @enderror"
                                required
                            >
                            <span>Email</span>
                        </label>

                        @error('email')
                            <x-alert type="error" size="sm" soft class="mb-4">{{ $message }}</x-alert>
                        @enderror

                        <label class="floating-label mb-4">
                            <input
                                type="password"
                                name="password"
                                placeholder="........"
                                class="input input-bordered @error('password') input-error @enderror"
                                required
                            >
                            <span>Password</span>
                        </label>

                        @error('password')
                            <x-alert type="error" size="sm" soft class="mb-4">{{ $message }}</x-alert>
                        @else
                            <p class="text-xs text-base-content/60 mb-4">Min 8 chars: upper + lower + number + symbol.</p>
                        @enderror

                        <label class="floating-label mb-4">
                            <input
                                type="password"
                                name="password_confirmation"
                                placeholder="........"
                                class="input input-bordered"
                                required
                            >
                            <span>Confirm Password</span>
                        </label>

                        <x-turnstile />

                        <div class="form-control mt-6">
                            <button
                                type="submit"
                                class="btn btn-primary w-full"
                            >
                                Register
                            </button>
                        </div>
                    </form>

                    <div class="divider">OR</div>
                    <p class="text-center text-sm">
                        Already have an account?
                        <a href="/login" class="link link-primary">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layout>
