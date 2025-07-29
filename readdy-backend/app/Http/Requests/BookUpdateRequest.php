<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
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
        $bookId = $this->route('book'); // Get the book ID from the route

        return [
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'language' => 'sometimes|required|string|max:10',
            'page_count' => 'nullable|integer|min:1',
            'word_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:5000',
            'excerpt' => 'nullable|string|max:2000',
            'cover_image' => 'nullable|string|max:500',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'original_price' => 'nullable|numeric|min:0|max:999999.99',
            'is_free' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_new_release' => 'boolean',
            'status' => 'sometimes|required|in:draft,published,archived',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The book title is required.',
            'author.required' => 'The author name is required.',
            'price.required' => 'The book price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'isbn.unique' => 'This ISBN is already registered by another book.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
}
