'use client';

import { useState } from 'react';
import EbookReader from './EbookReader';

export default function UserLibrary() {
  const [selectedBook, setSelectedBook] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');

  const books = [
    {
      id: '1',
      title: 'The Psychology of Money',
      author: 'Morgan Housel',
      cover: 'https://readdy.ai/api/search-image?query=psychology%20of%20money%20book%20cover%20design%2C%20financial%20concepts%2C%20modern%20minimalist%20style%2C%20professional%20layout%2C%20clean%20typography%2C%20business%20book%20aesthetics%2C%20white%20background&width=200&height=300&seq=reading-1&orientation=portrait',
      progress: 75,
      totalPages: 256,
      currentPage: 192,
      lastRead: '2024-01-15',
      status: 'reading',
      genre: 'Finance',
      rating: 5,
      content: {
        chapters: [
          { title: 'Chapter 1: No One\'s Crazy', pages: 20 },
          { title: 'Chapter 2: Luck & Risk', pages: 18 },
          { title: 'Chapter 3: Never Enough', pages: 22 },
          { title: 'Chapter 4: Confounding Compounding', pages: 16 },
          { title: 'Chapter 5: Getting Wealthy vs. Staying Wealthy', pages: 24 }
        ]
      }
    },
    {
      id: '2',
      title: 'Atomic Habits',
      author: 'James Clear',
      cover: 'https://readdy.ai/api/search-image?query=atomic%20habits%20book%20cover%20design%2C%20habit%20formation%20concept%2C%20scientific%20approach%2C%20modern%20design%2C%20motivational%20theme%2C%20self-help%20book%20style%2C%20clean%20background&width=200&height=300&seq=reading-2&orientation=portrait',
      progress: 45,
      totalPages: 320,
      currentPage: 144,
      lastRead: '2024-01-14',
      status: 'reading',
      genre: 'Self-Help',
      rating: 5,
      content: {
        chapters: [
          { title: 'Chapter 1: The Surprising Power of Atomic Habits', pages: 25 },
          { title: 'Chapter 2: How Your Habits Shape Your Identity', pages: 22 },
          { title: 'Chapter 3: How to Build Better Habits in 4 Simple Steps', pages: 28 },
          { title: 'Chapter 4: The Man Who Didn\'t Look Right', pages: 18 }
        ]
      }
    },
    {
      id: '3',
      title: 'The Midnight Library',
      author: 'Matt Haig',
      cover: 'https://readdy.ai/api/search-image?query=midnight%20library%20book%20cover%20design%2C%20mystical%20library%20setting%2C%20dark%20blue%20tones%2C%20magical%20atmosphere%2C%20literary%20fiction%20style%2C%20dreamy%20aesthetic%2C%20minimalist%20design&width=200&height=300&seq=reading-3&orientation=portrait',
      progress: 100,
      totalPages: 288,
      currentPage: 288,
      lastRead: '2024-01-10',
      status: 'completed',
      genre: 'Fiction',
      rating: 4,
      content: {
        chapters: [
          { title: 'Chapter 1: The Midnight Library', pages: 20 },
          { title: 'Chapter 2: The Root Library', pages: 25 },
          { title: 'Chapter 3: A Life of Extreme Caution', pages: 30 },
          { title: 'Chapter 4: Singing', pages: 22 }
        ]
      }
    },
    {
      id: '4',
      title: 'Dune',
      author: 'Frank Herbert',
      cover: 'https://readdy.ai/api/search-image?query=dune%20book%20cover%20design%2C%20desert%20planet%20landscape%2C%20science%20fiction%20epic%2C%20futuristic%20elements%2C%20sand%20dunes%2C%20dramatic%20composition%2C%20classic%20sci-fi%20aesthetic&width=200&height=300&seq=reading-4&orientation=portrait',
      progress: 0,
      totalPages: 688,
      currentPage: 0,
      lastRead: null,
      status: 'unread',
      genre: 'Science Fiction',
      rating: 5,
      content: {
        chapters: [
          { title: 'Book One: Dune', pages: 200 },
          { title: 'Book Two: Muad\'Dib', pages: 244 },
          { title: 'Book Three: The Prophet', pages: 244 }
        ]
      }
    },
    {
      id: '5',
      title: 'The Alchemist',
      author: 'Paulo Coelho',
      cover: 'https://readdy.ai/api/search-image?query=the%20alchemist%20book%20cover%20design%2C%20mystical%20desert%20journey%2C%20philosophical%20adventure%2C%20spiritual%20quest%2C%20warm%20golden%20tones%2C%20minimalist%20design%2C%20inspirational%20theme&width=200&height=300&seq=reading-5&orientation=portrait',
      progress: 100,
      totalPages: 163,
      currentPage: 163,
      lastRead: '2024-01-05',
      status: 'completed',
      genre: 'Philosophy',
      rating: 4,
      content: {
        chapters: [
          { title: 'Part One: The Boy\'s Dream', pages: 40 },
          { title: 'Part Two: The Journey to Egypt', pages: 123 }
        ]
      }
    },
    {
      id: '6',
      title: 'Where the Crawdads Sing',
      author: 'Delia Owens',
      cover: 'https://readdy.ai/api/search-image?query=where%20the%20crawdads%20sing%20book%20cover%20design%2C%20marshland%20setting%2C%20nature%20imagery%2C%20mystery%20novel%20aesthetic%2C%20watercolor%20style%2C%20atmospheric%20mood%2C%20literary%20fiction&width=200&height=300&seq=reading-6&orientation=portrait',
      progress: 20,
      totalPages: 384,
      currentPage: 77,
      lastRead: '2024-01-12',
      status: 'reading',
      genre: 'Mystery',
      rating: 4,
      content: {
        chapters: [
          { title: 'Chapter 1: Ma', pages: 15 },
          { title: 'Chapter 2: The Shack', pages: 18 },
          { title: 'Chapter 3: Chase', pages: 20 },
          { title: 'Chapter 4: School', pages: 24 }
        ]
      }
    }
  ];

  const filteredBooks = books.filter(book => {
    const matchesSearch = book.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         book.author.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         book.genre.toLowerCase().includes(searchQuery.toLowerCase());
    
    const matchesFilter = filterStatus === 'all' || book.status === filterStatus;
    
    return matchesSearch && matchesFilter;
  });

  const getStatusColor = (status) => {
    switch(status) {
      case 'reading': return 'bg-blue-100 text-blue-800';
      case 'completed': return 'bg-green-100 text-green-800';
      case 'unread': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusText = (status) => {
    switch(status) {
      case 'reading': return 'Currently Reading';
      case 'completed': return 'Completed';
      case 'unread': return 'Not Started';
      default: return status;
    }
  };

  const handleBookClick = (book) => {
    setSelectedBook(book);
  };

  const handleCloseReader = () => {
    setSelectedBook(null);
  };

  if (selectedBook) {
    return <EbookReader book={selectedBook} onClose={handleCloseReader} />;
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">My Reading Library</h1>
        <p className="text-gray-600">Manage and read your ebook collection</p>
      </div>

      {/* Search and Filter */}
      <div className="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div className="flex flex-col md:flex-row gap-4">
          <div className="flex-1 relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i className="ri-search-line text-gray-400"></i>
            </div>
            <input
              type="text"
              placeholder="Search books, authors, or genres..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div className="flex space-x-2">
            {['all', 'reading', 'completed', 'unread'].map((status) => (
              <button
                key={status}
                onClick={() => setFilterStatus(status)}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap cursor-pointer ${
                  filterStatus === status
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                {status === 'all' ? 'All Books' : getStatusText(status)}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Books Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {filteredBooks.map((book) => (
          <div
            key={book.id}
            onClick={() => handleBookClick(book)}
            className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer"
          >
            <div className="relative">
              <img
                src={book.cover}
                alt={book.title}
                className="w-full h-64 object-cover object-top"
              />
              
              {/* Progress Overlay */}
              {book.progress > 0 && (
                <div className="absolute bottom-0 left-0 right-0 bg-black/50 p-2">
                  <div className="flex items-center space-x-2">
                    <div className="flex-1 bg-gray-300 rounded-full h-2">
                      <div
                        className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                        style={{ width: `${book.progress}%` }}
                      ></div>
                    </div>
                    <span className="text-white text-xs">{book.progress}%</span>
                  </div>
                </div>
              )}

              {/* Status Badge */}
              <div className={`absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(book.status)}`}>
                {getStatusText(book.status)}
              </div>
            </div>

            <div className="p-4">
              <h3 className="font-semibold text-gray-900 mb-1 line-clamp-2">
                {book.title}
              </h3>
              <p className="text-gray-600 text-sm mb-2">{book.author}</p>
              <p className="text-gray-500 text-xs mb-3">{book.genre}</p>

              {/* Reading Stats */}
              <div className="flex items-center justify-between text-sm text-gray-500 mb-3">
                <span>Page {book.currentPage} of {book.totalPages}</span>
                <div className="flex items-center space-x-1">
                  {[...Array(5)].map((_, i) => (
                    <i
                      key={i}
                      className={`ri-star-${i < book.rating ? 'fill' : 'line'} text-yellow-400 text-xs`}
                    ></i>
                  ))}
                </div>
              </div>

              {/* Last Read */}
              {book.lastRead && (
                <p className="text-xs text-gray-400 mb-3">
                  Last read: {new Date(book.lastRead).toLocaleDateString()}
                </p>
              )}

              {/* Action Button */}
              <button className="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm whitespace-nowrap">
                {book.status === 'unread' ? 'Start Reading' : 
                 book.status === 'completed' ? 'Read Again' : 'Continue Reading'}
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Empty State */}
      {filteredBooks.length === 0 && (
        <div className="text-center py-12">
          <div className="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <i className="ri-book-line text-gray-400 text-2xl"></i>
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">No books found</h3>
          <p className="text-gray-600">Try adjusting your search or filter criteria</p>
        </div>
      )}
    </div>
  );
}