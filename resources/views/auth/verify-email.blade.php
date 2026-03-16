<x-layout>
    <x-slot:title>
        Verify your email
    </x-slot:title>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col">
            <div class="card w-96 bg-base-100">
                <div class="card-body text-center">
                    <div class="text-5xl mb-4">📬</div>
                    <h1 class="text-xl font-bold mb-2">
                        Check your email
                    </h1>

                    <p class="text-base-content/60 text-sm mb-6">
                        We've sent a verification link to your email.
                        Please click the link to continue.
                    </p>


                    <form method="POST" action="/email/verification-notification">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm w-full"
                        >
                            Forward email
                        </button>
                    </form>

                    <form method="POST" action="/logout" class="mt-2">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-ghost btn-sm w-full"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
