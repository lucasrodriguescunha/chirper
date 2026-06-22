<x-layout>
    <x-slot:title>
        Bookmarks
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl sm:text-3xl font-bold mt-6 sm:mt-8">Bookmarks</h1>

        <div class="space-y-4 mt-6">
            @forelse ($chirps as $chirp)
                <x-chirps.chirp :chirp="$chirp"/>
            @empty
                <p class="text-center text-base-content/60 py-8">No bookmarks yet. Save a chirp to find it here.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $chirps->links() }}
        </div>
    </div>
</x-layout>
