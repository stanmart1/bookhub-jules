
'use client';

import { useState } from 'react';
import BookCard from './BookCard';
import Link from 'next/link';

export default function PersonalizedRecommendations() {
  const [selectedGenre, setSelectedGenre] = useState('all');

  const genres = [
    { id: 'all', name: 'All Recommendations' },
    { id: 'fiction', name: 'Fiction' },
    { id: 'non-fiction', name: 'Non-Fiction' },
    { id: 'self-help', name: 'Self-Help' },
    { id: 'mystery', name: 'Mystery' }
  ];

  const recommendations = [
    {
      id: '1',
      title: 'The Silent Patient',
      author: 'Alex Michaelides',
      price: 15.99,
      rating: 4,
      reviewCount: 1543,
      cover: 'https://readdy.ai/api/search-image?query=the%20silent%20patient%20book%20cover%20design%2C%20psychological%20thriller%2C%20mental%20health%20themes%2C%20mystery%20novel%2C%20dark%20atmospheric%20design%2C%20bestseller%20thriller&width=200&height=300&seq=rec-1&orientation=portrait',
      isAvailable: true,
      genre: 'mystery',
      reason: 'Based on your love for psychological thrillers'
    },
    {
      id: '2',
      title: 'Educated',
      author: 'Tara Westover',
      price: 17.99,
      rating: 5,
      reviewCount: 2876,
      cover: 'https://readdy.ai/api/search-image?query=educated%20book%20cover%20design%2C%20memoir%20about%20education%2C%20inspiring%20true%20story%2C%20family%20drama%2C%20educational%20journey%2C%20award%20winning%20memoir&width=200&height=300&seq=rec-2&orientation=portrait',
      isAvailable: true,
      genre: 'non-fiction',
      reason: 'Recommended because you enjoyed memoirs'
    },
    {
      id: '3',
      title: 'The 7 Habits of Highly Effective People',
      author: 'Stephen Covey',
      price: 16.99,
      rating: 4,
      reviewCount: 3421,
      cover: 'https://readdy.ai/api/search-image?query=7%20habits%20highly%20effective%20people%20book%20cover%20design%2C%20self-improvement%2C%20business%20success%2C%20leadership%20development%2C%20classic%20self-help%20design&width=200&height=300&seq=rec-3&orientation=portrait',
      isAvailable: true,
      genre: 'self-help',
      reason: 'Perfect for your personal development journey'
    },
    {
      id: '4',
      title: 'The Song of Achilles',
      author: 'Madeline Miller',
      price: 14.99,
      rating: 5,
      reviewCount: 1987,
      cover: 'https://readdy.ai/api/search-image?query=song%20of%20achilles%20book%20cover%20design%2C%20greek%20mythology%2C%20epic%20romance%2C%20historical%20fiction%2C%20classical%20literature%2C%20award%20winning%20novel&width=200&height=300&seq=rec-4&orientation=portrait',
      isAvailable: true,
      genre: 'fiction',
      reason: 'You\'ll love this mythological retelling'
    },
    {
      id: '5',
      title: 'Sapiens',
      author: 'Yuval Noah Harari',
      price: 18.99,
      rating: 4,
      reviewCount: 4321,
      cover: 'https://readdy.ai/api/search-image?query=sapiens%20book%20cover%20design%2C%20human%20history%2C%20anthropology%2C%20evolution%2C%20scientific%20approach%2C%20bestseller%20non-fiction%2C%20educational%20design&width=200&height=300&seq=rec-5&orientation=portrait',
      isAvailable: true,
      genre: 'non-fiction',
      reason: 'Based on your interest in human psychology'
    },
    {
      id: '6',
      title: 'The Alchemist',
      author: 'Paulo Coelho',
      price: 13.99,
      rating: 4,
      reviewCount: 2789,
      cover: 'https://readdy.ai/api/search-image?query=the%20alchemist%20book%20cover%20design%2C%20philosophical%20journey%2C%20spiritual%20quest%2C%20desert%20adventure%2C%20inspirational%20fiction%2C%20classic%20literature&width=200&height=300&seq=rec-6&orientation=portrait',
      isAvailable: true,
      genre: 'fiction',
      reason: 'A timeless tale of following your dreams'
    }
  ];

  const filteredRecommendations = selectedGenre === 'all' 
    ? recommendations 
    : recommendations.filter(book => book.genre === selectedGenre);

  return (
    <div className="py-16 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-gray-900 mb-2">Recommended for You</h2>
          <p className="text-gray-600">Personalized picks based on your reading history and preferences</p>
        </div>
        
        {/* Genre Filter */}
        <div className="flex justify-center mb-8">
          <div className="flex space-x-1 bg-white p-1 rounded-full border border-gray-200">
            {genres.map((genre) => (
              <button
                key={genre.id}
                onClick={() => setSelectedGenre(genre.id)}
                className={`px-6 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap cursor-pointer ${
                  selectedGenre === genre.id
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-600 hover:text-gray-800'
                }`}
              >
                {genre.name}
              </button>
            ))}
          </div>
        </div>
        
        {/* Recommendations Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredRecommendations.map((book) => (
            <div key={book.id} className="relative">
              <BookCard {...book} />
              <div className="absolute top-2 left-2 bg-blue-600 text-white px-2 py-1 rounded text-xs">
                Recommended
              </div>
              <div className="mt-2 text-center">
                <p className="text-sm text-gray-600 italic">{book.reason}</p>
              </div>
            </div>
          ))}
        </div>
        
        {/* Call to Action */}
        <div className="text-center mt-12">
          <div className="bg-white rounded-lg shadow-md p-8 inline-block">
            <h3 className="text-xl font-semibold text-gray-900 mb-2">Want Better Recommendations?</h3>
            <p className="text-gray-600 mb-4">Rate more books and update your reading preferences</p>
            <Link 
              href="/preferences"
              className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer whitespace-nowrap"
            >
              Update Preferences
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
