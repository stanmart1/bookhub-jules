
'use client';

import { useState } from 'react';
import BookCard from './BookCard';

export default function NewReleases() {
  const [currentPage, setCurrentPage] = useState(0);
  const booksPerPage = 4;

  const newBooks = [
    {
      id: '1',
      title: 'The Seven Husbands of Evelyn Hugo',
      author: 'Taylor Jenkins Reid',
      price: 16.99,
      originalPrice: 22.99,
      rating: 5,
      reviewCount: 1876,
      cover: 'https://readdy.ai/api/search-image?query=seven%20husbands%20evelyn%20hugo%20book%20cover%20design%2C%20hollywood%20glamour%2C%20vintage%20aesthetic%2C%20celebrity%20romance%2C%20elegant%20typography%2C%20bestseller%20design&width=200&height=300&seq=new-1&orientation=portrait',
      isAvailable: true
    },
    {
      id: '2',
      title: 'Project Hail Mary',
      author: 'Andy Weir',
      price: 17.99,
      originalPrice: 24.99,
      rating: 5,
      reviewCount: 2341,
      cover: 'https://readdy.ai/api/search-image?query=project%20hail%20mary%20book%20cover%20design%2C%20space%20adventure%2C%20scientific%20thriller%2C%20modern%20sci-fi%20aesthetic%2C%20space%20exploration%20theme%2C%20award%20winning%20design&width=200&height=300&seq=new-2&orientation=portrait',
      isAvailable: true
    },
    {
      id: '3',
      title: 'Klara and the Sun',
      author: 'Kazuo Ishiguro',
      price: 15.99,
      rating: 4,
      reviewCount: 1234,
      cover: 'https://readdy.ai/api/search-image?query=klara%20and%20the%20sun%20book%20cover%20design%2C%20artificial%20intelligence%20theme%2C%20futuristic%20drama%2C%20literary%20fiction%2C%20minimalist%20modern%20design%2C%20nobel%20prize%20winner&width=200&height=300&seq=new-3&orientation=portrait',
      isAvailable: true
    },
    {
      id: '4',
      title: 'The Invisible Life of Addie LaRue',
      author: 'V.E. Schwab',
      price: 18.99,
      rating: 4,
      reviewCount: 1654,
      cover: 'https://readdy.ai/api/search-image?query=invisible%20life%20addie%20larue%20book%20cover%20design%2C%20fantasy%20romance%2C%20magical%20realism%2C%20gothic%20aesthetic%2C%20elegant%20dark%20tones%2C%20bestseller%20fantasy&width=200&height=300&seq=new-4&orientation=portrait',
      isAvailable: true
    },
    {
      id: '5',
      title: 'The Sanatorium',
      author: 'Sarah Pearse',
      price: 14.99,
      rating: 4,
      reviewCount: 987,
      cover: 'https://readdy.ai/api/search-image?query=the%20sanatorium%20book%20cover%20design%2C%20psychological%20thriller%2C%20alpine%20setting%2C%20mystery%20novel%2C%20atmospheric%20horror%2C%20suspenseful%20design&width=200&height=300&seq=new-5&orientation=portrait',
      isAvailable: true
    },
    {
      id: '6',
      title: 'Malibu Rising',
      author: 'Taylor Jenkins Reid',
      price: 16.99,
      rating: 4,
      reviewCount: 1432,
      cover: 'https://readdy.ai/api/search-image?query=malibu%20rising%20book%20cover%20design%2C%20california%20beach%20setting%2C%20family%20drama%2C%201980s%20aesthetic%2C%20summer%20vibes%2C%20contemporary%20fiction&width=200&height=300&seq=new-6&orientation=portrait',
      isAvailable: true
    },
    {
      id: '7',
      title: 'The Thursday Murder Club',
      author: 'Richard Osman',
      price: 15.99,
      rating: 4,
      reviewCount: 1765,
      cover: 'https://readdy.ai/api/search-image?query=thursday%20murder%20club%20book%20cover%20design%2C%20cozy%20mystery%2C%20retirement%20home%20setting%2C%20detective%20fiction%2C%20british%20mystery%2C%20charming%20illustration&width=200&height=300&seq=new-7&orientation=portrait',
      isAvailable: true
    },
    {
      id: '8',
      title: 'The Guest List',
      author: 'Lucy Foley',
      price: 13.99,
      rating: 4,
      reviewCount: 2109,
      cover: 'https://readdy.ai/api/search-image?query=the%20guest%20list%20book%20cover%20design%2C%20wedding%20thriller%2C%20psychological%20suspense%2C%20dark%20secrets%2C%20elegant%20mystery%20design%2C%20bestseller%20thriller&width=200&height=300&seq=new-8&orientation=portrait',
      isAvailable: true
    }
  ];

  const totalPages = Math.ceil(newBooks.length / booksPerPage);
  const currentBooks = newBooks.slice(
    currentPage * booksPerPage,
    (currentPage + 1) * booksPerPage
  );

  const nextPage = () => {
    setCurrentPage((prev) => (prev + 1) % totalPages);
  };

  const prevPage = () => {
    setCurrentPage((prev) => (prev - 1 + totalPages) % totalPages);
  };

  return (
    <div className="py-16 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-3xl font-bold text-gray-900 mb-2">New Releases</h2>
            <p className="text-gray-600">Discover the latest books from your favorite authors</p>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <span className="text-sm text-gray-500">
                {currentPage + 1} of {totalPages}
              </span>
            </div>
            
            <div className="flex space-x-2">
              <button
                onClick={prevPage}
                disabled={currentPage === 0}
                className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
              >
                <i className="ri-arrow-left-line"></i>
              </button>
              <button
                onClick={nextPage}
                disabled={currentPage === totalPages - 1}
                className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
              >
                <i className="ri-arrow-right-line"></i>
              </button>
            </div>
          </div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {currentBooks.map((book) => (
            <BookCard key={book.id} {...book} />
          ))}
        </div>
        
        {/* Pagination Dots */}
        <div className="flex justify-center mt-8 space-x-2">
          {Array.from({ length: totalPages }, (_, i) => (
            <button
              key={i}
              onClick={() => setCurrentPage(i)}
              className={`w-3 h-3 rounded-full transition-colors cursor-pointer ${
                i === currentPage ? 'bg-blue-600' : 'bg-gray-300 hover:bg-gray-400'
              }`}
            />
          ))}
        </div>
      </div>
    </div>
  );
}
