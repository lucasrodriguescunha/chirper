@props([])

@php
    $siteKey = config('services.turnstile.site_key');
@endphp

@if ($siteKey)
    <div class="flex justify-center mb-4">
        <div class="cf-turnstile" data-sitekey="{{ $siteKey }}"></div>
    </div>
    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce
    @error('cf-turnstile-response')
        <div class="label -mt-2 mb-2">
            <span class="label-text-alt text-error">{{ $message }}</span>
        </div>
    @enderror
@endif
