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
            'attachment' => 'nullable|file|max:2048|mimes:jpg,webp,jpeg,png,gif,pdf,txt',
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
            'attachment.mimes' => 'Only images, PDFs and text files are allowed',
        ];
    }
}
