@props(['chirp'])

@auth
    <form method="POST" action="/chirps/{{ $chirp->id }}/comments" class="form-control mt-2">
        @csrf
        <div class="flex gap-2">
            <input
                type="text"
                name="body"
                placeholder="Write a comment..."
                maxlength="255"
                data-counter
                required
                class="input input-bordered flex-1"
            />
            <button type="submit" class="btn btn-primary">
                Send
            </button>
        </div>
    </form>
@endauth
