<x-layout>
    <x-slot:title>
        Reset password
    </x-slot:title>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col">
            <div class="card w-96 bg-base-100">
                <div class="card-body text-center">

                    <h1 class="text-xl font-bold mb-2">
                        Reset your password
                    </h1>

                    @if ($errors->any())
                        <x-alert type="error" size="sm" soft class="mb-4 text-left">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <input type="email" name="email"
                               placeholder="Email"
                               class="input input-bordered w-full mb-4 @error('email') input-error @enderror"
                               required>

                        <input type="password" name="password"
                               placeholder="New password"
                               class="input input-bordered w-full mb-4 @error('password') input-error @enderror"
                               required>

                        <input type="password" name="password_confirmation"
                               placeholder="Confirm password"
                               class="input input-bordered w-full mb-4"
                               required>

                        <button class="btn btn-primary w-full">
                            Reset password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
