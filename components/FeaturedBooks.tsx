
'use client';

import { useState } from 'react';
import Link from 'next/link';

export default function FeaturedBooks() {
  const [selectedCategory, setSelectedCategory] = useState('trending');

  const categories = [
    { id: 'trending', name: 'Trending Now', icon: 'ri-fire-line' },
    { id: 'bestsellers', name: 'Bestsellers', icon: 'ri-star-line' },
    { id: 'new', name: 'New Releases', icon: 'ri-flashlight-line' },
    { id: 'recommended', name: 'Staff Picks', icon: 'ri-heart-line' }
  ];

  const books = {
    trending: [
      {
        id: '1',
        title: 'Tomorrow, and Tomorrow, and Tomorrow',
        author: 'Gabrielle Zevin',
        price: 18.99,
        rating: 5,
        category: 'Fiction',
        cover: 'https://readdy.ai/api/search-image?query=tomorrow%20and%20tomorrow%20book%20cover%20design%2C%20gaming%20culture%2C%20friendship%20story%2C%20contemporary%20fiction%2C%20modern%20literary%20design%2C%20bestseller%20aesthetic&width=250&height=350&seq=featured-1&orientation=portrait'
      },
      {
        id: '2',
        title: 'The Atlas Six',
        author: 'Olivie Blake',
        price: 16.99,
        rating: 4,
        category: 'Fantasy',
        cover: 'https://readdy.ai/api/search-image?query=atlas%20six%20book%20cover%20design%2C%20dark%20academia%2C%20magical%20society%2C%20fantasy%20novel%2C%20mystical%20elements%2C%20elegant%20gothic%20design&width=250&height=350&seq=featured-2&orientation=portrait'
      },
      {
        id: '3',
        title: 'Lessons in Chemistry',
        author: 'Bonnie Garmus',
        price: 17.99,
        rating: 5,
        category: 'Historical Fiction',
        cover: 'https://readdy.ai/api/search-image?query=lessons%20in%20chemistry%20book%20cover%20design%2C%201960s%20setting%2C%20female%20scientist%2C%20retro%20aesthetic%2C%20colorful%20vintage%20design%2C%20feminist%20themes&width=250&height=350&seq=featured-3&orientation=portrait'
      },
      {
        id: '4',
        title: 'The Seven Moons of Maali Almeida',
        author: 'Shehan Karunatilaka',
        price: 19.99,
        rating: 4,
        category: 'Literary Fiction',
        cover: 'https://readdy.ai/api/search-image?query=seven%20moons%20maali%20almeida%20book%20cover%20design%2C%20sri%20lankan%20setting%2C%20magical%20realism%2C%20booker%20prize%20winner%2C%20vibrant%20cultural%20design&width=250&height=350&seq=featured-4&orientation=portrait'
      }
    ],
    bestsellers: [
      {
        id: '5',
        title: 'Fourth Wing',
        author: 'Rebecca Yarros',
        price: 15.99,
        rating: 5,
        category: 'Romance Fantasy',
        cover: 'https://readdy.ai/api/search-image?query=fourth%20wing%20book%20cover%20design%2C%20dragon%20riders%2C%20military%20academy%2C%20fantasy%20romance%2C%20epic%20adventure%2C%20dark%20romantic%20fantasy&width=250&height=350&seq=featured-5&orientation=portrait'
      },
      {
        id: '6',
        title: 'Happy Place',
        author: 'Emily Henry',
        price: 16.99,
        rating: 4,
        category: 'Romance',
        cover: 'https://readdy.ai/api/search-image?query=happy%20place%20book%20cover%20design%2C%20beach%20house%20setting%2C%20friend%20group%2C%20summer%20romance%2C%20contemporary%20romance%2C%20bright%20cheerful%20design&width=250&height=350&seq=featured-6&orientation=portrait'
      },
      {
        id: '7',
        title: 'The Woman in Me',
        author: 'Britney Spears',
        price: 20.99,
        rating: 4,
        category: 'Memoir',
        cover: 'https://readdy.ai/api/search-image?query=the%20woman%20in%20me%20britney%20spears%20book%20cover%20design%2C%20celebrity%20memoir%2C%20pop%20culture%2C%20personal%20story%2C%20elegant%20portrait%20design&width=250&height=350&seq=featured-7&orientation=portrait'
      },
      {
        id: '8',
        title: 'Iron Flame',
        author: 'Rebecca Yarros',
        price: 18.99,
        rating: 5,
        category: 'Fantasy',
        cover: 'https://readdy.ai/api/search-image?query=iron%20flame%20book%20cover%20design%2C%20dragons%2C%20war%20college%2C%20fantasy%20sequel%2C%20fire%20elements%2C%20epic%20fantasy%20adventure&width=250&height=350&seq=featured-8&orientation=portrait'
      }
    ],
    new: [
      {
        id: '9',
        title: 'The Wager',
        author: 'David Grann',
        price: 19.99,
        rating: 4,
        category: 'Non-fiction',
        cover: 'https://readdy.ai/api/search-image?query=the%20wager%20book%20cover%20design%2C%20shipwreck%20survival%2C%20historical%20adventure%2C%20maritime%20disaster%2C%20dramatic%20ocean%20scene%2C%20nautical%20theme&width=250&height=350&seq=featured-9&orientation=portrait'
      },
      {
        id: '10',
        title: 'The Covenant of Water',
        author: 'Abraham Verghese',
        price: 21.99,
        rating: 5,
        category: 'Literary Fiction',
        cover: 'https://readdy.ai/api/search-image?query=covenant%20of%20water%20book%20cover%20design%2C%20indian%20family%20saga%2C%20multigenerational%20story%2C%20water%20symbolism%2C%20literary%20fiction%2C%20cultural%20heritage&width=250&height=350&seq=featured-10&orientation=portrait'
      },
      {
        id: '11',
        title: 'The Rachel Incident',
        author: 'Caroline O\'Donoghue',
        price: 16.99,
        rating: 4,
        category: 'Coming of Age',
        cover: 'https://readdy.ai/api/search-image?query=rachel%20incident%20book%20cover%20design%2C%20coming%20of%20age%2C%20irish%20setting%2C%20young%20adult%20relationships%2C%20contemporary%20fiction%2C%20modern%20literary%20design&width=250&height=350&seq=featured-11&orientation=portrait'
      },
      {
        id: '12',
        title: 'Demon Copperhead',
        author: 'Barbara Kingsolver',
        price: 18.99,
        rating: 5,
        category: 'Literary Fiction',
        cover: 'https://readdy.ai/api/search-image?query=demon%20copperhead%20book%20cover%20design%2C%20appalachian%20setting%2C%20pulitzer%20prize%20winner%2C%20social%20issues%2C%20contemporary%20american%20fiction&width=250&height=350&seq=featured-12&orientation=portrait'
      }
    ],
    recommended: [
      {
        id: '13',
        title: 'Babel',
        author: 'R.F. Kuang',
        price: 17.99,
        rating: 5,
        category: 'Fantasy',
        cover: 'https://readdy.ai/api/search-image?query=babel%20book%20cover%20design%2C%20dark%20academia%2C%20oxford%20university%2C%20translation%20magic%2C%20victorian%20setting%2C%20linguistic%20fantasy&width=250&height=350&seq=featured-13&orientation=portrait'
      },
      {
        id: '14',
        title: 'The School for Good Mothers',
        author: 'Jessamine Chan',
        price: 16.99,
        rating: 4,
        category: 'Dystopian Fiction',
        cover: 'https://readdy.ai/api/search-image?query=school%20for%20good%20mothers%20book%20cover%20design%2C%20dystopian%20society%2C%20parenting%20themes%2C%20surveillance%20state%2C%20contemporary%20dystopia&width=250&height=350&seq=featured-14&orientation=portrait'
      },
      {
        id: '15',
        title: 'Mexican Gothic',
        author: 'Silvia Moreno-Garcia',
        price: 15.99,
        rating: 4,
        category: 'Horror',
        cover: 'https://readdy.ai/api/search-image?query=mexican%20gothic%20book%20cover%20design%2C%20haunted%20mansion%2C%20gothic%20horror%2C%20mexican%20setting%2C%20supernatural%20mystery%2C%20dark%20atmospheric%20design&width=250&height=350&seq=featured-15&orientation=portrait'
      },
      {
        id: '16',
        title: 'The Midnight Girls',
        author: 'Alicia Jasinska',
        price: 17.99,
        rating: 4,
        category: 'Fantasy',
        cover: 'https://readdy.ai/api/search-image?query=midnight%20girls%20book%20cover%20design%2C%20slavic%20folklore%2C%20witch%20mythology%2C%20dark%20fairy%20tale%2C%20eastern%20european%20fantasy%2C%20mystical%20forest&width=250&height=350&seq=featured-16&orientation=portrait'
      }
    ]
  };

  const currentBooks = books[selectedCategory];

  return (
    <div className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="text-5xl font-bold text-gray-900 mb-6">Featured Books</h2>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Discover our carefully curated selection of must-read books across all genres
          </p>
        </div>
        
        {/* Category Tabs */}
        <div className="flex justify-center mb-12">
          <div className="flex space-x-1 bg-gray-100 p-1 rounded-2xl">
            {categories.map((category) => (
              <button
                key={category.id}
                onClick={() => setSelectedCategory(category.id)}
                className={`flex items-center space-x-2 px-6 py-3 rounded-2xl font-medium transition-all duration-300 cursor-pointer whitespace-nowrap ${
                  selectedCategory === category.id
                    ? 'bg-blue-600 text-white shadow-lg transform scale-105'
                    : 'text-gray-600 hover:text-gray-800 hover:bg-gray-200'
                }`}
              >
                <i className={`${category.icon} text-lg`}></i>
                <span>{category.name}</span>
              </button>
            ))}
          </div>
        </div>
        
        {/* Books Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {currentBooks.map((book) => (
            <div key={book.id} className="group cursor-pointer">
              <div className="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div className="relative">
                  <img
                    src={book.cover}
                    alt={book.title}
                    className="w-full h-72 object-cover object-top"
                  />
                  <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full px-3 py-1">
                    <div className="flex items-center space-x-1">
                      <i className="ri-star-fill text-yellow-400 text-sm"></i>
                      <span className="text-sm font-medium">{book.rating}</span>
                    </div>
                  </div>
                  <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4">
                    <span className="bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                      {book.category}
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
