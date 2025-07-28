
'use client';

import { useState } from 'react';
import BookCard from '@/components/BookCard';

export default function LibrarySection() {
  const [activeTab, setActiveTab] = useState('all');

  const books = [
    {
      id: '1',
      title: 'The Psychology of Money',
      author: 'Morgan Housel',
      price: 19.99,
      rating: 5,
      reviewCount: 1243,
      cover: 'https://readdy.ai/api/search-image?query=psychology%20of%20money%20book%20cover%20design%2C%20financial%20concepts%2C%20modern%20minimalist%20style%2C%20professional%20layout%2C%20clean%20typography%2C%20business%20book%20design&width=200&height=300&seq=lib-1&orientation=portrait',
      isAvailable: true,
      progress: 75
    },
    {
      id: '2',
      title: 'Atomic Habits',
      author: 'James Clear',
      price: 16.99,
      originalPrice: 24.99,
      rating: 5,
      reviewCount: 2156,
      cover: 'https://readdy.ai/api/search-image?query=atomic%20habits%20book%20cover%20design%2C%20habit%20formation%20concept%2C%20scientific%20approach%2C%20modern%20design%2C%20motivational%20theme%2C%20self-help%20book%20style&width=200&height=300&seq=lib-2&orientation=portrait',
      isAvailable: true,
      progress: 45
    },
    {
      id: '3',
      title: 'The Midnight Library',
      author: 'Matt Haig',
      price: 14.99,
      rating: 4,
      reviewCount: 987,
      cover: 'https://readdy.ai/api/search-image?query=midnight%20library%20book%20cover%20design%2C%20mystical%20library%20setting%2C%20dark%20blue%20tones%2C%20magical%20atmosphere%2C%20literary%20fiction%20style%2C%20dreamy%20aesthetic&width=200&height=300&seq=lib-3&orientation=portrait',
      isAvailable: true,
      progress: 92
    },
    {
      id: '4',
      title: 'Dune',
      author: 'Frank Herbert',
      price: 18.99,
      rating: 5,
      reviewCount: 3421,
      cover: 'https://readdy.ai/api/search-image?query=dune%20book%20cover%20design%2C%20desert%20planet%20landscape%2C%20science%20fiction%20epic%2C%20futuristic%20elements%2C%20sand%20dunes%2C%20dramatic%20composition&width=200&height=300&seq=lib-4&orientation=portrait',
      isAvailable: true
    },
    {
      id: '5',
      title: 'The Alchemist',
      author: 'Paulo Coelho',
      price: 13.99,
      rating: 4,
      reviewCount: 2789,
      cover: 'https://readdy.ai/api/search-image?query=the%20alchemist%20book%20cover%20design%2C%20mystical%20desert%20journey%2C%20philosophical%20adventure%2C%20spiritual%20quest%2C%20warm%20golden%20tones%2C%20minimalist%20design&width=200&height=300&seq=lib-5&orientation=portrait',
      isAvailable: true
    },
    {
      id: '6',
      title: 'Where the Crawdads Sing',
      author: 'Delia Owens',
      price: 15.99,
      rating: 4,
      reviewCount: 1876,
      cover: 'https://readdy.ai/api/search-image?query=where%20the%20crawdads%20sing%20book%20cover%20design%2C%20marshland%20setting%2C%20nature%20imagery%2C%20mystery%20novel%20aesthetic%2C%20watercolor%20style%2C%20atmospheric%20mood&width=200&height=300&seq=lib-6&orientation=portrait',
      isAvailable: true
    }
  ];

  const tabs = [
    { id: 'all', label: 'All Books', count: books.length },
    { id: 'reading', label: 'Currently Reading', count: 3 },
    { id: 'completed', label: 'Completed', count: 2 },
    { id: 'wishlist', label: 'Wishlist', count: 1 }
  ];

  const filteredBooks = () => {
    switch(activeTab) {
      case 'reading':
        return books.filter(book => book.progress !== undefined && book.progress < 100);
      case 'completed':
        return books.filter(book => book.progress === undefined || book.progress === 100);
      case 'wishlist':
        return books.slice(0, 1);
      default:
        return books;
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">My Library</h2>
        
        {/* Tab Navigation */}
        <div className="flex space-x-1 bg-gray-100 p-1 rounded-full">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`px-4 py-2 text-sm font-medium rounded-full transition-colors whitespace-nowrap cursor-pointer ${
                activeTab === tab.id
                  ? 'bg-blue-600 text-white'
                  : 'text-gray-600 hover:text-gray-800'
              }`}
            >
              {tab.label} ({tab.count})
            </button>
          ))}
        </div>
      </div>

      {/* Books Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {filteredBooks().map((book) => (
          <BookCard key={book.id} {...book} />
        ))}
      </div>

      {/* Load More */}
      <div className="mt-6 text-center">
        <button className="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer whitespace-nowrap">
          Load More Books
        </button>
      </div>
    </div>
  );
}
