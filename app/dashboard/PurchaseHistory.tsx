
'use client';

import { useState } from 'react';
import { formatDistanceToNow } from 'date-fns';

export default function PurchaseHistory() {
  const [activeTab, setActiveTab] = useState('recent');

  const purchases = [
    {
      id: 'ORD-2024-001',
      date: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
      items: [
        {
          title: 'The Psychology of Money',
          author: 'Morgan Housel',
          price: 19.99,
          cover: 'https://readdy.ai/api/search-image?query=psychology%20of%20money%20book%20cover%20design%2C%20financial%20concepts%2C%20modern%20minimalist%20style%2C%20professional%20layout%2C%20clean%20typography&width=60&height=90&seq=purchase-1&orientation=portrait'
        }
      ],
      total: 19.99,
      status: 'completed'
    },
    {
      id: 'ORD-2024-002',
      date: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000),
      items: [
        {
          title: 'Atomic Habits',
          author: 'James Clear',
          price: 16.99,
          cover: 'https://readdy.ai/api/search-image?query=atomic%20habits%20book%20cover%20design%2C%20habit%20formation%20concept%2C%20scientific%20approach%2C%20modern%20design%2C%20motivational%20theme&width=60&height=90&seq=purchase-2&orientation=portrait'
        },
        {
          title: 'The Midnight Library',
          author: 'Matt Haig',
          price: 14.99,
          cover: 'https://readdy.ai/api/search-image?query=midnight%20library%20book%20cover%20design%2C%20mystical%20library%20setting%2C%20dark%20blue%20tones%2C%20magical%20atmosphere%2C%20literary%20fiction%20style&width=60&height=90&seq=purchase-3&orientation=portrait'
        }
      ],
      total: 31.98,
      status: 'completed'
    },
    {
      id: 'ORD-2024-003',
      date: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000),
      items: [
        {
          title: 'Dune',
          author: 'Frank Herbert',
          price: 18.99,
          cover: 'https://readdy.ai/api/search-image?query=dune%20book%20cover%20design%2C%20desert%20planet%20landscape%2C%20science%20fiction%20epic%2C%20futuristic%20elements%2C%20sand%20dunes&width=60&height=90&seq=purchase-4&orientation=portrait'
        }
      ],
      total: 18.99,
      status: 'completed'
    }
  ];

  const wishlist = [
    {
      id: 'w1',
      title: 'Project Hail Mary',
      author: 'Andy Weir',
      price: 17.99,
      originalPrice: 24.99,
      cover: 'https://readdy.ai/api/search-image?query=project%20hail%20mary%20book%20cover%20design%2C%20space%20adventure%2C%20scientific%20thriller%2C%20modern%20sci-fi%20aesthetic%2C%20space%20exploration%20theme&width=60&height=90&seq=wishlist-1&orientation=portrait',
      dateAdded: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000)
    },
    {
      id: 'w2',
      title: 'The Seven Husbands of Evelyn Hugo',
      author: 'Taylor Jenkins Reid',
      price: 16.99,
      cover: 'https://readdy.ai/api/search-image?query=seven%20husbands%20evelyn%20hugo%20book%20cover%20design%2C%20hollywood%20glamour%2C%20vintage%20aesthetic%2C%20celebrity%20romance%2C%20elegant%20typography&width=60&height=90&seq=wishlist-2&orientation=portrait',
      dateAdded: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000)
    },
    {
      id: 'w3',
      title: 'Klara and the Sun',
      author: 'Kazuo Ishiguro',
      price: 15.99,
      cover: 'https://readdy.ai/api/search-image?query=klara%20and%20the%20sun%20book%20cover%20design%2C%20artificial%20intelligence%20theme%2C%20futuristic%20drama%2C%20literary%20fiction%2C%20minimalist%20modern%20design&width=60&height=90&seq=wishlist-3&orientation=portrait',
      dateAdded: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)
    }
  ];

  const tabs = [
    { id: 'recent', label: 'Recent Purchases' },
    { id: 'wishlist', label: 'Wishlist' }
  ];

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Purchase History & Wishlist</h2>
        
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
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Purchase History */}
      {activeTab === 'recent' && (
        <div className="space-y-4">
          {purchases.map((purchase) => (
            <div key={purchase.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-3">
                <div>
                  <h3 className="font-medium text-gray-900">Order {purchase.id}</h3>
                  <p className="text-sm text-gray-500">
                    {formatDistanceToNow(purchase.date, { addSuffix: true })}
                  </p>
                </div>
                <div className="text-right">
                  <p className="font-semibold text-gray-900">${purchase.total}</p>
                  <span className="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                    {purchase.status}
                  </span>
                </div>
              </div>
              
              <div className="space-y-2">
                {purchase.items.map((item, index) => (
                  <div key={index} className="flex items-center space-x-3">
                    <img 
                      src={item.cover} 
                      alt={item.title}
                      className="w-12 h-18 object-cover object-top rounded"
                    />
                    <div className="flex-1">
                      <h4 className="font-medium text-sm text-gray-900">{item.title}</h4>
                      <p className="text-xs text-gray-600">{item.author}</p>
                    </div>
                    <p className="text-sm font-medium text-gray-900">${item.price}</p>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Wishlist */}
      {activeTab === 'wishlist' && (
        <div className="space-y-4">
          {wishlist.map((item) => (
            <div key={item.id} className="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
              <img 
                src={item.cover} 
                alt={item.title}
                className="w-12 h-18 object-cover object-top rounded"
              />
              <div className="flex-1">
                <h4 className="font-medium text-sm text-gray-900">{item.title}</h4>
                <p className="text-xs text-gray-600">{item.author}</p>
                <p className="text-xs text-gray-500 mt-1">
                  Added {formatDistanceToNow(item.dateAdded, { addSuffix: true })}
                </p>
              </div>
              <div className="text-right">
                <div className="flex items-center space-x-2">
                  <span className="text-sm font-medium text-gray-900">${item.price}</span>
                  {item.originalPrice && (
                    <span className="text-xs text-gray-500 line-through">${item.originalPrice}</span>
                  )}
                </div>
                <div className="flex space-x-2 mt-2">
                  <button className="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors cursor-pointer whitespace-nowrap">
                    Add to Cart
                  </button>
                  <button className="px-3 py-1 text-gray-600 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors cursor-pointer whitespace-nowrap">
                    Remove
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
