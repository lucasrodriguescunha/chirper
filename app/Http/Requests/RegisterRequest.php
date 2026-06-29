<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
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
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username'],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'cf-turnstile-response' => ['nullable', 'string', new Turnstile],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'username.required' => 'Please pick a username.',
            'username.regex' => 'Username may contain only letters, numbers, and underscores.',
            'username.unique' => 'This username is taken.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username must be 30 characters or less.',
            'email.required' => 'Please enter your email.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}
