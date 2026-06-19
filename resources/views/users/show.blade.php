<x-layout>
    <x-slot:title>
        {{ $user->name }}
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow mt-8">
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="avatar">
                        <div class="size-20 rounded-full">
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}'s avatar"/>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h1 class="text-2xl font-bold">{{ $user->name }}</h1>

                            @auth
                                @if ($user->id !== auth()->id())
                                    @if (auth()->user()->isFollowing($user))
                                        <form method="POST" action="{{ route('follows.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm">Unfollow</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('follows.store', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Follow</button>
                                        </form>
                                    @endif
                                @endif
                            @endauth
                        </div>

                        @if ($user->bio)
                            <p class="mt-2 text-base-content/80">{{ $user->bio }}</p>
                        @endif

                        <div class="flex gap-4 mt-3 text-sm text-base-content/70">
                            <span><strong>{{ $user->following()->count() }}</strong> Following</span>
                            <span><strong>{{ $user->followers()->count() }}</strong> Followers</span>
                            <span><strong>{{ $user->chirps()->count() }}</strong> Chirps</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-bold mt-8">Chirps</h2>

        <div class="space-y-4 mt-4">
            @forelse ($chirps as $chirp)
                <x-chirps.chirp :chirp="$chirp"/>
            @empty
                <p class="text-center text-base-content/60 py-8">No chirps yet.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $chirps->links() }}
        </div>
    </div>
</x-layout>
