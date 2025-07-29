<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'language' => 'required|string|max:10|default:en',
            'page_count' => 'nullable|integer|min:1',
            'word_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:5000',
            'excerpt' => 'nullable|string|max:2000',
            'cover_image' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0|max:999999.99',
            'original_price' => 'nullable|numeric|min:0|max:999999.99',
            'is_free' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_new_release' => 'boolean',
            'status' => 'required|in:draft,published,archived',
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
            'isbn.unique' => 'This ISBN is already registered.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
} 