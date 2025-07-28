
'use client';

import Link from 'next/link';
import { useState } from 'react';

export default function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);

  return (
    <header className="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <div className="flex-shrink-0">
            <Link href="/" className="text-2xl font-bold text-blue-600" style={{ fontFamily: 'Pacifico, serif' }}>
              BookHaven
            </Link>
          </div>

          {/* Search Bar */}
          <div className="flex-1 max-w-2xl mx-8 relative">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i className="ri-search-line text-gray-400 text-sm"></i>
              </div>
              <input
                type="text"
                placeholder="Search books, authors, genres..."
                className="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
              />
              <button 
                onClick={() => setIsSearchOpen(!isSearchOpen)}
                className="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer"
              >
                <i className="ri-equalizer-line text-gray-400 text-sm"></i>
              </button>
            </div>
            
            {/* Advanced Search Filters */}
            {isSearchOpen && (
              <div className="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
                <div className="grid grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Genre</label>
                    <select className="w-full p-2 border border-gray-300 rounded-md text-sm pr-8">
                      <option>All Genres</option>
                      <option>Fiction</option>
                      <option>Non-Fiction</option>
                      <option>Mystery</option>
                      <option>Romance</option>
                      <option>Sci-Fi</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                    <select className="w-full p-2 border border-gray-300 rounded-md text-sm pr-8">
                      <option>Any Price</option>
                      <option>Under $10</option>
                      <option>$10 - $20</option>
                      <option>$20 - $50</option>
                      <option>Over $50</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <select className="w-full p-2 border border-gray-300 rounded-md text-sm pr-8">
                      <option>Any Rating</option>
                      <option>4+ Stars</option>
                      <option>3+ Stars</option>
                      <option>2+ Stars</option>
                    </select>
                  </div>
                </div>
                <div className="mt-4 flex justify-end space-x-2">
                  <button className="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm whitespace-nowrap cursor-pointer">
                    Clear Filters
                  </button>
                  <button className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm whitespace-nowrap cursor-pointer">
                    Apply Filters
                  </button>
                </div>
              </div>
            )}
          </div>

          {/* Navigation Icons */}
          <div className="flex items-center space-x-4">
            <Link href="/dashboard" className="p-2 text-gray-600 hover:text-gray-800 relative cursor-pointer">
              <div className="w-6 h-6 flex items-center justify-center">
                <i className="ri-user-line text-lg"></i>
              </div>
            </Link>
            <Link href="/wishlist" className="p-2 text-gray-600 hover:text-gray-800 relative cursor-pointer">
              <div className="w-6 h-6 flex items-center justify-center">
                <i className="ri-heart-line text-lg"></i>
              </div>
              <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                3
              </span>
            </Link>
            <Link href="/cart" className="p-2 text-gray-600 hover:text-gray-800 relative cursor-pointer">
              <div className="w-6 h-6 flex items-center justify-center">
                <i className="ri-shopping-cart-line text-lg"></i>
              </div>
              <span className="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                2
              </span>
            </Link>
            
            {/* Mobile Menu Button */}
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="md:hidden p-2 text-gray-600 hover:text-gray-800 cursor-pointer"
            >
              <div className="w-6 h-6 flex items-center justify-center">
                <i className={`ri-${isMenuOpen ? 'close' : 'menu'}-line text-lg`}></i>
              </div>
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {isMenuOpen && (
          <div className="md:hidden border-t border-gray-200 py-4">
            <div className="flex flex-col space-y-2">
              <Link href="/dashboard" className="px-4 py-2 text-gray-600 hover:text-gray-800 cursor-pointer">
                Dashboard
              </Link>
              <Link href="/wishlist" className="px-4 py-2 text-gray-600 hover:text-gray-800 cursor-pointer">
                Wishlist
              </Link>
              <Link href="/cart" className="px-4 py-2 text-gray-600 hover:text-gray-800 cursor-pointer">
                Cart
              </Link>
            </div>
          </div>
        )}
      </div>
    </header>
  );
}
