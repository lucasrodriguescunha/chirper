<x-layout>
    <x-slot:title>
        #{{ $tag->name }}
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <div class="mt-6 sm:mt-8">
            <a href="/" class="link link-hover text-sm text-base-content/60">← Back to feed</a>
            <h1 class="text-2xl sm:text-3xl font-bold mt-2 break-words">#{{ $tag->name }}</h1>
            <p class="text-sm text-base-content/60 mt-1">
                {{ $chirps->total() }} {{ Str::plural('chirp', $chirps->total()) }}
            </p>
        </div>

        <div class="space-y-4 mt-8">
            @forelse ($chirps as $chirp)
                <x-chirps.chirp :chirp="$chirp"/>
            @empty
                <div class="hero py-12">
                    <div class="hero-content text-center">
                        <p class="text-base-content/60">
                            No chirps tagged with #{{ $tag->name }} yet.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $chirps->links() }}
        </div>
    </div>
</x-layout>
