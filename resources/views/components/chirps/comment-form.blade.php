@props(['chirp'])

@auth
    <form method="POST" action="/chirps/{{ $chirp->id }}/comments" class="form-control mt-2">
        @csrf
        <div class="flex flex-col sm:flex-row gap-2">
            <input
                type="text"
                name="body"
                placeholder="Write a comment..."
                maxlength="255"
                data-counter
                required
                class="input input-bordered flex-1 w-full"
            />
            <button type="submit" class="btn btn-primary w-full sm:w-auto">
                Send
            </button>
        </div>
    </form>
@endauth
