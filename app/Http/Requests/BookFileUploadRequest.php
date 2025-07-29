<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookFileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:epub,pdf,mobi|max:100000', // 100MB max
            'file_type' => 'required|in:epub,pdf,mobi,audio',
            'is_primary' => 'boolean',
            'duration' => 'nullable|integer|min:1', // For audio books
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'The file must be an EPUB, PDF, or MOBI file.',
            'file.max' => 'The file size cannot exceed 100MB.',
            'file_type.required' => 'The file type is required.',
            'file_type.in' => 'The file type must be epub, pdf, mobi, or audio.',
            'duration.integer' => 'The duration must be a valid number.',
            'duration.min' => 'The duration must be at least 1 second.',
        ];
    }
} 