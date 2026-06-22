<x-layout>
    <x-slot:title>
        Sign In
    </x-slot:title>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col w-full px-4 sm:px-0">
            <div class="card w-full max-w-sm bg-base-100">
                <div class="card-body">

                    <h1 class="text-xl mt-1 font-bold text-center mb-6">Welcome Back</h1>

                    @if (session('status'))
                        <x-alert type="success" size="sm" soft class="mb-4">{{ session('status') }}</x-alert>
                    @endif

                    <form method="POST" action="/login">
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
                            <x-alert type="error" size="sm" soft class="mb-4">{{ $message }}</x-alert>
                        @enderror

                        <label class="floating-label mb-4">
                            <input type="password"
                                   name="password"
                                   placeholder="••••••••"
                                   class="input input-bordered @error('password') input-error @enderror"
                                   required>
                            <span>Password</span>
                        </label>
                        @error('password')
                            <x-alert type="error" size="sm" soft class="mb-4">{{ $message }}</x-alert>
                        @enderror

                        <div class="form-control mt-4">
                            <label class="label cursor-pointer justify-start">
                                <input type="checkbox"
                                       name="remember"
                                       class="checkbox">
                                <span class="label-text ml-2">Remember me</span>
                            </label>
                        </div>

                        <x-turnstile />

                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary w-full">
                                Sign In
                            </button>
                        </div>
                    </form>

                    <div class="divider">OR</div>

                    <div class="text-center text-sm space-y-2">
                        <p>
                            Don't have an account?
                            <a href="/register" class="link link-primary">Register</a>
                        </p>

                        <p>
                            Forgot your password?
                            <a href="/forgot-password" class="link link-primary">Recover</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
