<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fiction',
                'slug' => 'fiction',
                'description' => 'Imaginative stories and novels',
                'icon' => 'ri-book-line',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'name' => 'Non-Fiction',
                'slug' => 'non-fiction',
                'description' => 'Factual and educational books',
                'icon' => 'ri-file-text-line',
                'color' => '#10B981',
                'sort_order' => 2,
            ],
            [
                'name' => 'Mystery',
                'slug' => 'mystery',
                'description' => 'Suspense and detective stories',
                'icon' => 'ri-search-line',
                'color' => '#F59E0B',
                'sort_order' => 3,
            ],
            [
                'name' => 'Romance',
                'slug' => 'romance',
                'description' => 'Love stories and romantic fiction',
                'icon' => 'ri-heart-line',
                'color' => '#EF4444',
                'sort_order' => 4,
            ],
            [
                'name' => 'Science Fiction',
                'slug' => 'science-fiction',
                'description' => 'Futuristic and speculative fiction',
                'icon' => 'ri-rocket-line',
                'color' => '#8B5CF6',
                'sort_order' => 5,
            ],
            [
                'name' => 'Fantasy',
                'slug' => 'fantasy',
                'description' => 'Magical and supernatural stories',
                'icon' => 'ri-magic-line',
                'color' => '#EC4899',
                'sort_order' => 6,
            ],
            [
                'name' => 'Self-Help',
                'slug' => 'self-help',
                'description' => 'Personal development and improvement',
                'icon' => 'ri-user-line',
                'color' => '#06B6D4',
                'sort_order' => 7,
            ],
            [
                'name' => 'Biography',
                'slug' => 'biography',
                'description' => 'Life stories and memoirs',
                'icon' => 'ri-user-star-line',
                'color' => '#84CC16',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
