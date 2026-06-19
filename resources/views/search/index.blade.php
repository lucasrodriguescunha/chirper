<x-layout>
    <x-slot:title>
        Search
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mt-8">Search</h1>

        <form method="GET" action="{{ route('search') }}" class="mt-4">
            <div class="flex gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Search chirps and users..."
                    class="input input-bordered flex-1"
                    autofocus
                />
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        @if ($q === '')
            <p class="text-base-content/60 mt-6">Type a query to find chirps or users.</p>
        @else
            <h2 class="text-xl font-semibold mt-8">Users</h2>
            @if ($users->isEmpty())
                <p class="text-base-content/60 mt-2">No users matched "{{ $q }}".</p>
            @else
                <ul class="mt-2 space-y-2">
                    @foreach ($users as $user)
                        <li class="card bg-base-100">
                            <a href="{{ route('users.show', $user) }}" class="flex items-center gap-3 p-3">
                                <img src="{{ $user->avatarUrl() }}" class="size-10 rounded-full" alt="{{ $user->name }}"/>
                                <div>
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    @if ($user->bio)
                                        <div class="text-sm text-base-content/60 line-clamp-1">{{ $user->bio }}</div>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <h2 class="text-xl font-semibold mt-8">Chirps</h2>
            @if ($chirps->isEmpty())
                <p class="text-base-content/60 mt-2">No chirps matched "{{ $q }}".</p>
            @else
                <div class="space-y-4 mt-2">
                    @foreach ($chirps as $chirp)
                        <x-chirps.chirp :chirp="$chirp"/>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-layout>
