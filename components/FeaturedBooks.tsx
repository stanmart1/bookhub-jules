
'use client';

import Link from 'next/link';
import { useFeaturedBooks } from '../hooks/useFeaturedBooks';

export default function FeaturedBooks() {
  const { books, loading, error } = useFeaturedBooks();

  if (loading) {
    return (
      <div className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-5xl font-bold text-gray-900 mb-6">Featured Books</h2>
            <p className="text-xl text-gray-600">Loading featured books...</p>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-5xl font-bold text-gray-900 mb-6">Featured Books</h2>
            <p className="text-xl text-red-600">Error loading featured books: {error}</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="text-5xl font-bold text-gray-900 mb-6">Featured Books</h2>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Discover our carefully curated selection of must-read books across all genres
          </p>
        </div>
        
        {/* Books Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {books.map((book) => (
            <div key={book.id} className="group cursor-pointer">
              <div className="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div className="relative">
                  <img
                    src={book.cover_image || '/placeholder-book.jpg'}
                    alt={book.title}
                    className="w-full h-72 object-cover object-top"
                  />
                  <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full px-3 py-1">
                    <div className="flex items-center space-x-1">
                      <i className="ri-star-fill text-yellow-400 text-sm"></i>
                      <span className="text-sm font-medium">{parseFloat(book.rating_average).toFixed(1)}</span>
                    </div>
                  </div>
                  <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4">
                    <span className="bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                      {book.categories[0]?.name || 'Fiction'}
                    </span>
                  </div>
                </div>
                
                <div className="p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                    {book.title}
                  </h3>
                  <p className="text-gray-600 mb-4">by {book.author}</p>
                  <div className="flex items-center justify-between">
                    <span className="text-2xl font-bold text-blue-600">${book.price}</span>
                    <button className="bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition-colors cursor-pointer whitespace-nowrap">
                      Add to Cart
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
        
        <div className="text-center mt-12">
          <Link
            href="/books"
            className="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 cursor-pointer whitespace-nowrap"
          >
            <span>Browse All Books</span>
            <i className="ri-arrow-right-line"></i>
          </Link>
        </div>
      </div>
    </div>
  );
}
