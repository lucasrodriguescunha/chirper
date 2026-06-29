<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    protected int $maxAttempts = 3;

    protected int $decaySeconds = 900;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'cf-turnstile-response' => ['nullable', 'string', new Turnstile],
        ];
    }

    /**
     * Perform additional validation after the base rules pass.
     *
     * @throws ValidationException
     */
    public function after(): array
    {
        return [
            function () {
                $this->checkTooManyAttempts();
            },
        ];
    }

    /**
     * @throws ValidationException
     */
    public function checkTooManyAttempts(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Please try again in {$this->formatWaitTime($seconds)}.",
        ]);
    }

    public function clearAttempts(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    public function hitAttempt(): void
    {
        RateLimiter::hit($this->throttleKey(), $this->decaySeconds);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email'))).'|'.$this->ip();
    }

    protected function formatWaitTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "$seconds second".($seconds !== 1 ? 's' : '');
        }

        if ($seconds < 3600) {
            $minutes = (int) ceil($seconds / 60);

            return "$minutes minute".($minutes !== 1 ? 's' : '');
        }

        $hours = (int) ceil($seconds / 3600);

        return "$hours hour".($hours !== 1 ? 's' : '');
    }
}
