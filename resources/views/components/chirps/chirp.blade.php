@props(['chirp'])
@use('Illuminate\Support\Facades\Storage')

<div class="card bg-base-100">
    <div class="card-body">
        <div class="flex space-x-3">
            @if ($chirp->user)
                <a href="{{ route('users.show', $chirp->user) }}" class="shrink-0">
                    <div class="avatar">
                        <div class="size-10 rounded-full">
                            <img
                                src="{{ $chirp->user->avatarUrl() }}"
                                alt="{{ $chirp->user->name }}'s avatar"
                                class="object-cover w-full h-full rounded-full"
                            />
                        </div>
                    </div>
                </a>
            @else
                <div class="avatar placeholder">
                    <div class="size-10 rounded-full">
                        <img
                            src="https://avatars.laravel.cloud/f61123d5-0b27-434c-a4ae-c653c7fc9ed6?vibe=stealth"
                            alt="Anonymous User"
                            class="rounded-full"
                        />
                    </div>
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2 w-full">
                    <div class="flex flex-wrap items-center gap-x-1 min-w-0">
                        @if ($chirp->user)
                            <a href="{{ route('users.show', $chirp->user) }}" class="text-sm font-semibold hover:underline truncate max-w-[60vw]">
                                {{ $chirp->user->name }}
                            </a>
                        @else
                            <span class="text-sm font-semibold">Anonymous</span>
                        @endif

                        <span class="text-base-content/60">•</span>

                        @if ($chirp->updated_at->gt($chirp->created_at->addSeconds(5)))
                            <span class="text-xs sm:text-sm text-base-content/60 italic">
                                Edited {{ $chirp->updated_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-xs sm:text-sm text-base-content/60">
                                Published {{ $chirp->created_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>

                    @can('update', $chirp)
                        <div class="flex gap-1 shrink-0">
                            <a href="/chirps/{{ $chirp->id }}/edit" class="btn btn-ghost">
                                Edit
                            </a>

                            <form method="POST" action="/chirps/{{ $chirp->id }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('Are you sure you want to delete this chirp?')"
                                    class="btn btn-ghost text-error"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>

                <p class="mt-1 break-words">
                    {{ $chirp->message }}
                </p>

                @if ($chirp->attachments?->path && Storage::disk('public')->exists($chirp->attachments->path))
                    <img class="mt-1 max-w-full h-auto rounded-md" src="{{ Storage::url($chirp->attachments->path) }}" alt=""/>
                @endif

                <div class="flex items-center gap-3 mt-3">
                    @php
                        $userReaction = auth()->check()
                            ? $chirp->likes->where('user_id', auth()->id())->first()?->type
                            : null;
                    @endphp

                    <button
                        class="reaction-button hover:scale-110 transition {{ $userReaction === 'like' ? 'text-red-600' : '' }}"
                        data-chirp-id="{{ $chirp->id }}"
                        data-type="like"
                    >
                        <x-feathericon-heart class="w-5 h-5"/>
                        <span>{{ $chirp->likes->where('type', 'like')->count() }}</span>
                    </button>

                    <button
                        class="reaction-button hover:scale-110 transition {{ $userReaction === 'dislike' ? 'text-red-600' : '' }}"
                        data-chirp-id="{{ $chirp->id }}"
                        data-type="dislike"
                    >
                        <x-ri-thumb-down-line class="w-5 h-5"/>
                        <span>{{ $chirp->likes->where('type', 'dislike')->count() }}</span>
                    </button>
                </div>

                <x-chirps.comments :chirp="$chirp" />
            </div>
        </div>
    </div>
</div>
