@props(['chirp'])
@use('Illuminate\Support\Facades\Storage')

<div class="card bg-base-100">
    <div class="card-body">
        <div class="flex space-x-3">
            @if ($chirp->user)
                <div class="avatar">
                    <div class="size-10 rounded-full">
                        <img
                            src="https://avatars.laravel.cloud/{{ urlencode($chirp->user->email) }}?vibe=ocean"
                            alt="{{ $chirp->user->name }}'s avatar"
                            class="rounded-full"
                        />
                    </div>
                </div>
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
                <div class="flex justify-between w-full">
                    <div class="flex items-center space-x-1">
                        <span class="text-sm font-semibold">
                            {{ $chirp->user ? $chirp->user->name : 'Anonymous' }}
                        </span>

                        <span class="text-base-content/60">•</span>

                        @if ($chirp->updated_at->gt($chirp->created_at->addSeconds(5)))
                            <span class="text-sm text-base-content/60 italic">
                                Edited {{ $chirp->updated_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-sm text-base-content/60">
                                Published {{ $chirp->created_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>

                    @can('update', $chirp)
                        <div class="flex gap-1">
                            <a href="/chirps/{{ $chirp->id }}/edit" class="btn btn-ghost btn-xs">
                                Edit
                            </a>

                            <form method="POST" action="/chirps/{{ $chirp->id }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('Are you sure you want to delete this chirp?')"
                                    class="btn btn-ghost btn-xs text-error"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>

                <p class="mt-1">
                    {{ $chirp->message }}
                </p>

                @if ($chirp->attachments?->path && Storage::disk('public')->exists($chirp->attachments->path))
                    @if (in_array($chirp->attachments->type, ['txt', 'pdf', 'svg', 'gif']))
                        <a href="{{ Storage::url($chirp->attachments->path) }}" download="true">Baixar conteúdo</a>
                    @else
                        <img class="mt-1" src="{{ Storage::url($chirp->attachments->path) }}" alt=""/>
                    @endif
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
                    </button>

                    <button
                        class="reaction-button hover:scale-110 transition {{ $userReaction === 'dislike' ? 'text-red-600' : '' }}"
                        data-chirp-id="{{ $chirp->id }}"
                        data-type="dislike"
                    >
                        <x-ri-thumb-down-line class="w-5 h-5"/>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
