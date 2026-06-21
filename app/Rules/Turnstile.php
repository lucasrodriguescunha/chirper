<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret_key');

        if (empty($secret)) {
            return;
        }

        if (empty($value) || ! is_string($value)) {
            $fail('Please complete the CAPTCHA challenge.');
            return;
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post(self::VERIFY_URL, [
                'secret' => $secret,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

        if (! $response->ok() || $response->json('success') !== true) {
            $fail('CAPTCHA verification failed. Please try again.');
        }
    }
}
