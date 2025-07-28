
'use client';

import { useState } from 'react';
import Link from 'next/link';

interface BookCardProps {
  id: string;
  title: string;
  author: string;
  price: number;
  originalPrice?: number;
  rating: number;
  reviewCount: number;
  cover: string;
  isAvailable: boolean;
  progress?: number;
  isWishlisted?: boolean;
}

export default function BookCard({ 
  id, 
  title, 
  author, 
  price, 
  originalPrice, 
  rating, 
  reviewCount, 
  cover, 
  isAvailable,
  progress,
  isWishlisted = false
}: BookCardProps) {
  const [isHovered, setIsHovered] = useState(false);
  const [wishlistStatus, setWishlistStatus] = useState(isWishlisted);

  const handleWishlist = () => {
    setWishlistStatus(!wishlistStatus);
  };

  const renderStars = (rating: number) => {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
      stars.push(
        <i
          key={i}
          className={`ri-star-${i <= rating ? 'fill' : 'line'} text-yellow-400`}
        ></i>
      );
    }
    return stars;
  };

  return (
    <div 
      className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      <div className="relative">
        <img 
          src={cover} 
          alt={title}
          className="w-full h-64 object-cover object-top"
        />
        
        {/* Progress Bar for Reading Books */}
        {progress !== undefined && (
          <div className="absolute bottom-0 left-0 right-0 bg-black/50 p-2">
            <div className="flex items-center space-x-2">
              <div className="flex-1 bg-gray-300 rounded-full h-2">
                <div 
                  className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                  style={{ width: `${progress}%` }}
                ></div>
              </div>
              <span className="text-white text-xs">{progress}%</span>
            </div>
          </div>
        )}

        {/* Hover Actions */}
        {isHovered && (
          <div className="absolute inset-0 bg-black/50 flex items-center justify-center space-x-2">
            <button 
              onClick={handleWishlist}
              className="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors cursor-pointer"
            >
              <div className="w-5 h-5 flex items-center justify-center">
                <i className={`ri-heart-${wishlistStatus ? 'fill text-red-500' : 'line text-gray-600'}`}></i>
              </div>
            </button>
            <Link 
              href={`/book/${id}`}
              className="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors cursor-pointer"
            >
              <div className="w-5 h-5 flex items-center justify-center">
                <i className="ri-eye-line text-gray-600"></i>
              </div>
            </Link>
            <button className="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors cursor-pointer">
              <div className="w-5 h-5 flex items-center justify-center">
                <i className="ri-shopping-cart-line"></i>
              </div>
            </button>
          </div>
        )}

        {/* Availability Badge */}
        {!isAvailable && (
          <div className="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
            Out of Stock
          </div>
        )}
      </div>

      <div className="p-4">
        <Link href={`/book/${id}`} className="cursor-pointer">
          <h3 className="font-semibold text-gray-900 mb-1 line-clamp-2 hover:text-blue-600 transition-colors">
            {title}
          </h3>
        </Link>
        <p className="text-gray-600 text-sm mb-2">{author}</p>
        
        {/* Rating */}
        <div className="flex items-center space-x-1 mb-2">
          <div className="flex space-x-1">
            {renderStars(rating)}
          </div>
          <span className="text-sm text-gray-500">({reviewCount})</span>
        </div>

        {/* Price */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <span className="text-lg font-bold text-gray-900">${price}</span>
            {originalPrice && (
              <span className="text-sm text-gray-500 line-through">${originalPrice}</span>
            )}
          </div>
          <div className="flex items-center space-x-2">
            {isAvailable ? (
              <span className="text-green-600 text-sm">In Stock</span>
            ) : (
              <span className="text-red-600 text-sm">Out of Stock</span>
            )}
          </div>
        </div>

        {/* Quick Actions */}
        <div className="mt-3 flex space-x-2">
          <button 
            className="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors text-sm whitespace-nowrap cursor-pointer"
            disabled={!isAvailable}
          >
            Add to Cart
          </button>
          <button className="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
            <div className="w-5 h-5 flex items-center justify-center">
              <i className="ri-more-line text-gray-600"></i>
            </div>
          </button>
        </div>
      </div>
    </div>
  );
}
