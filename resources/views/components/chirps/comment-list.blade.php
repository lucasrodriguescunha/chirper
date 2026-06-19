@props(['chirp'])

@foreach ($chirp->comments as $comment)
    @php
        $userReaction = auth()->check()
            ? $comment->likes->where('user_id', auth()->id())->first()?->type
            : null;
    @endphp

    <div class="flex items-start gap-2 mb-2" data-comment="{{ $comment->id }}">
        <img
            src="{{ $comment->user->avatarUrl() }}"
            class="size-7 rounded-full"
            alt="{{ $comment->user->name }}"
        />
        <div class="flex-1 bg-base-200 rounded-lg px-3 py-2 text-sm">
            <span class="font-semibold">{{ $comment->user->name }}</span>

            @if ($comment->updated_at->gt($comment->created_at->addSeconds(5)))
                <span class="text-xs text-base-content/60 italic">
                    Edited {{ $comment->updated_at->diffForHumans() }}
                </span>
            @else
                <span class="text-xs text-base-content/60">
                     Published {{ $comment->created_at->diffForHumans() }}
                </span>
            @endif

            <p class="mt-0.5" data-comment-body>{{ $comment->body }}</p>

            @if (auth()->id() === $comment->user_id)
                <form
                    method="POST"
                    action="/chirps/{{ $chirp->id }}/comments/{{ $comment->id }}"
                    class="mt-2 hidden form-control"
                    data-comment-edit-form
                >
                    @csrf
                    @method('PUT')
                    <textarea
                        name="body"
                        rows="2"
                        maxlength="255"
                        data-counter
                        class="textarea textarea-bordered textarea-sm w-full"
                    >{{ $comment->body }}</textarea>
                    <div class="flex justify-end gap-2 mt-1">
                        <button type="button" class="btn btn-ghost btn-xs" data-comment-edit-cancel>Cancel</button>
                        <button type="submit" class="btn btn-primary btn-xs">Save</button>
                    </div>
                </form>
            @endif

            {{-- Comment reactions --}}
            <div class="flex items-center gap-3 mt-2">
                <button
                    class="comment-reaction-button hover:scale-110 transition {{ $userReaction === 'like' ? 'text-red-600' : '' }}"
                    data-comment-id="{{ $comment->id }}"
                    data-type="like"
                >
                    <x-feathericon-heart class="w-4 h-4"/>
                    <span>{{ $comment->likes->where('type', 'like')->count() }}</span>
                </button>

                <button
                    class="comment-reaction-button hover:scale-110 transition {{ $userReaction === 'dislike' ? 'text-red-600' : '' }}"
                    data-comment-id="{{ $comment->id }}"
                    data-type="dislike"
                >
                    <x-ri-thumb-down-line class="w-4 h-4"/>
                    <span>{{ $comment->likes->where('type', 'dislike')->count() }}</span>
                </button>
            </div>
        </div>

        @if (auth()->id() === $comment->user_id)
            <div class="flex flex-col mt-1">
                <button type="button" class="btn btn-ghost btn-xs" data-comment-edit-toggle>✎</button>
                <form method="POST" action="/chirps/{{ $chirp->id }}/comments/{{ $comment->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-xs text-error">✕</button>
                </form>
            </div>
        @endif
    </div>
@endforeach
