<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'Tomorrow, and Tomorrow, and Tomorrow',
                'subtitle' => 'A Novel',
                'author' => 'Gabrielle Zevin',
                'isbn' => '9780593321201',
                'publisher' => 'Knopf',
                'publication_date' => '2022-07-05',
                'language' => 'en',
                'page_count' => 416,
                'word_count' => 120000,
                'description' => 'A modern love story about two friends finding their way through life, video games, and the ever-changing landscape of Los Angeles.',
                'excerpt' => 'On a bitter-cold day, in the December of his junior year at Harvard, Sam Masur exited a subway car and saw, amid the hordes of people waiting on the platform, Sadie Green.',
                'cover_image' => 'https://readdy.ai/api/search-image?query=tomorrow%20and%20tomorrow%20book%20cover%20design%2C%20gaming%20culture%2C%20friendship%20story%2C%20contemporary%20fiction%2C%20modern%20literary%20design%2C%20bestseller%20aesthetic&width=250&height=350&seq=featured-1&orientation=portrait',
                'price' => 18.99,
                'original_price' => 24.99,
                'is_free' => false,
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new_release' => false,
                'status' => 'published',
                'rating_average' => 4.5,
                'rating_count' => 1250,
                'view_count' => 5000,
                'download_count' => 1200,
                'categories' => ['fiction'],
            ],
            [
                'title' => 'The Atlas Six',
                'subtitle' => 'A Novel',
                'author' => 'Olivie Blake',
                'isbn' => '9781250776699',
                'publisher' => 'Tor Books',
                'publication_date' => '2022-03-01',
                'language' => 'en',
                'page_count' => 384,
                'word_count' => 110000,
                'description' => 'The Alexandrian Society, caretakers of lost knowledge from the greatest civilizations of antiquity, are the foremost secret society of magical academicians in the world.',
                'excerpt' => 'The Alexandrian Society was a secret society of magical academicians, the world\'s six most uniquely talented magicians.',
                'cover_image' => 'https://readdy.ai/api/search-image?query=atlas%20six%20book%20cover%20design%2C%20dark%20academia%2C%20magical%20society%2C%20fantasy%20novel%2C%20mystical%20elements%2C%20elegant%20gothic%20design&width=250&height=350&seq=featured-2&orientation=portrait',
                'price' => 16.99,
                'original_price' => 22.99,
                'is_free' => false,
                'is_featured' => true,
                'is_bestseller' => false,
                'is_new_release' => true,
                'status' => 'published',
                'rating_average' => 4.2,
                'rating_count' => 890,
                'view_count' => 3200,
                'download_count' => 750,
                'categories' => ['fantasy', 'fiction'],
            ],
            [
                'title' => 'Lessons in Chemistry',
                'subtitle' => 'A Novel',
                'author' => 'Bonnie Garmus',
                'isbn' => '9780385547345',
                'publisher' => 'Doubleday',
                'publication_date' => '2022-04-05',
                'language' => 'en',
                'page_count' => 400,
                'word_count' => 115000,
                'description' => 'Set in 1960s California, this blockbuster debut is the hilarious, idiosyncratic and uplifting story of a female scientist whose career is constantly derailed by the idea that a woman\'s place is in the home.',
                'excerpt' => 'Back in 1961, when women wore shirtwaist dresses and joined garden clubs and drove legions of children around in seatbeltless cars without giving it a second thought.',
                'cover_image' => 'https://readdy.ai/api/search-image?query=lessons%20in%20chemistry%20book%20cover%20design%2C%201960s%20setting%2C%20female%20scientist%2C%20retro%20aesthetic%2C%20colorful%20vintage%20design%2C%20feminist%20themes&width=250&height=350&seq=featured-3&orientation=portrait',
                'price' => 17.99,
                'original_price' => 23.99,
                'is_free' => false,
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new_release' => false,
                'status' => 'published',
                'rating_average' => 4.7,
                'rating_count' => 2100,
                'view_count' => 7800,
                'download_count' => 1800,
                'categories' => ['fiction'],
            ],
            [
                'title' => 'Atomic Habits',
                'subtitle' => 'An Easy & Proven Way to Build Good Habits & Break Bad Ones',
                'author' => 'James Clear',
                'isbn' => '9780735211292',
                'publisher' => 'Avery',
                'publication_date' => '2018-10-16',
                'language' => 'en',
                'page_count' => 320,
                'word_count' => 90000,
                'description' => 'No matter your goals, Atomic Habits offers a proven framework for improving every day. James Clear, one of the world\'s leading experts on habit formation.',
                'excerpt' => 'The fate of British Cycling changed one day in 2003. The organization, which was the governing body for professional cycling in Great Britain.',
                'cover_image' => 'https://readdy.ai/api/search-image?query=atomic%20habits%20book%20cover%20design%2C%20self%20help%2C%20productivity%2C%20minimalist%20design%2C%20blue%20and%20white%2C%20professional%20aesthetic&width=250&height=350&seq=featured-4&orientation=portrait',
                'price' => 15.99,
                'original_price' => 19.99,
                'is_free' => false,
                'is_featured' => false,
                'is_bestseller' => true,
                'is_new_release' => false,
                'status' => 'published',
                'rating_average' => 4.8,
                'rating_count' => 3500,
                'view_count' => 12000,
                'download_count' => 2800,
                'categories' => ['self-help', 'non-fiction'],
            ],
            [
                'title' => 'The Psychology of Money',
                'subtitle' => 'Timeless Lessons on Wealth, Greed, and Happiness',
                'author' => 'Morgan Housel',
                'isbn' => '9780857197689',
                'publisher' => 'Harriman House',
                'publication_date' => '2020-09-08',
                'language' => 'en',
                'page_count' => 256,
                'word_count' => 70000,
                'description' => 'Doing well with money isn\'t necessarily about what you know. It\'s about how you behave. And behavior is hard to teach, even to really smart people.',
                'excerpt' => 'Money is everywhere, it affects all of us, and confuses most of us. Everyone thinks about it a little differently.',
                'cover_image' => 'https://readdy.ai/api/search-image?query=psychology%20of%20money%20book%20cover%20design%2C%20finance%2C%20psychology%2C%20wealth%2C%20minimalist%20design%2C%20professional%20aesthetic&width=250&height=350&seq=featured-5&orientation=portrait',
                'price' => 14.99,
                'original_price' => 18.99,
                'is_free' => false,
                'is_featured' => false,
                'is_bestseller' => true,
                'is_new_release' => false,
                'status' => 'published',
                'rating_average' => 4.6,
                'rating_count' => 2800,
                'view_count' => 9500,
                'download_count' => 2200,
                'categories' => ['self-help', 'non-fiction'],
            ],
        ];

        foreach ($books as $bookData) {
            $categories = $bookData['categories'];
            unset($bookData['categories']);
            
            $book = Book::create($bookData);
            
            // Attach categories
            foreach ($categories as $categorySlug) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category) {
                    $book->categories()->attach($category->id, ['primary' => true]);
                }
            }
        }
    }
}
