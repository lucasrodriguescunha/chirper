<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChirpRequest extends FormRequest
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
            'message' => 'required|string|max:255',
            'attachment' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp,gif',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Please write something to chirp!',
            'message.max' => 'Chirps must be 255 characters or less',
            'attachment.max' => 'File must be under 2MB',
            'attachment.image' => 'Attachment must be an image (jpg, jpeg, png, webp, gif).',
            'attachment.mimes' => 'Only jpg, jpeg, png, webp, and gif images are allowed.',
        ];
    }
}
