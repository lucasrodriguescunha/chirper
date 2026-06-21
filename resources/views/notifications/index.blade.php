<x-layout>
    <x-slot:title>
        Notifications
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-2 mt-6 sm:mt-8">
            <h1 class="text-2xl sm:text-3xl font-bold">Notifications</h1>

            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost">Clear all</button>
                </form>
            @endif
        </div>

        <div class="space-y-2 mt-6">
            @forelse ($notifications as $notification)
                <div class="card bg-base-100">
                    <div class="card-body flex-row items-center justify-between gap-2">
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="flex-1 min-w-0">
                            <p class="break-words text-sm sm:text-base">{{ $notification->data['message'] ?? 'Notification' }}</p>
                            <p class="text-xs text-base-content/60 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </a>

                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost text-error">✕</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-base-content/60 text-center py-8">No notifications yet.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</x-layout>
