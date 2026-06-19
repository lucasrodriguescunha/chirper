@props(['chirp'])

<div class="mt-4 border-t border-base-200 pt-3">
    <x-chirps.comment-list :chirp="$chirp" />
    <x-chirps.comment-form :chirp="$chirp" />
</div>
