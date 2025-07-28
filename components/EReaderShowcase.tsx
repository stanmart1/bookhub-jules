
'use client';

import { useState } from 'react';
import Link from 'next/link';

export default function EReaderShowcase() {
  const [activeFeature, setActiveFeature] = useState(0);

  const features = [
    {
      id: 'reading',
      title: 'Immersive Reading Experience',
      description: 'Enjoy a distraction-free reading environment with customizable fonts, themes, and layouts. Our reader adapts to your preferences for the perfect reading experience.',
      icon: 'ri-book-open-line',
      image: 'https://readdy.ai/api/search-image?query=modern%20ebook%20reader%20interface%2C%20clean%20reading%20experience%2C%20customizable%20text%20display%2C%20digital%20book%20pages%2C%20comfortable%20reading%20layout%2C%20user-friendly%20design&width=600&height=400&seq=reader-1&orientation=landscape'
    },
    {
      id: 'customization',
      title: 'Personalized Settings',
      description: 'Adjust font size, line spacing, background colors, and reading modes. Switch between day and night themes, or create your own custom reading environment.',
      icon: 'ri-settings-3-line',
      image: 'https://readdy.ai/api/search-image?query=ebook%20reader%20customization%20panel%2C%20font%20settings%2C%20theme%20options%2C%20reading%20preferences%2C%20user%20interface%20controls%2C%20personalization%20features&width=600&height=400&seq=reader-2&orientation=landscape'
    },
    {
      id: 'annotations',
      title: 'Smart Annotations',
      description: 'Highlight text, add notes, and bookmark important passages. All your annotations are automatically synced across devices and searchable.',
      icon: 'ri-edit-line',
      image: 'https://readdy.ai/api/search-image?query=digital%20book%20with%20highlights%20and%20notes%2C%20annotation%20tools%2C%20bookmarks%2C%20note-taking%20interface%2C%20interactive%20reading%20features&width=600&height=400&seq=reader-3&orientation=landscape'
    },
    {
      id: 'progress',
      title: 'Reading Progress & Analytics',
      description: 'Track your reading speed, time spent, and progress through books. Set reading goals and get insights into your reading habits.',
      icon: 'ri-bar-chart-line',
      image: 'https://readdy.ai/api/search-image?query=reading%20progress%20dashboard%2C%20analytics%20charts%2C%20reading%20statistics%2C%20progress%20tracking%2C%20reading%20goals%20visualization%2C%20data%20insights&width=600&height=400&seq=reader-4&orientation=landscape'
    }
  ];

  return (
    <div className="py-20 bg-gradient-to-br from-blue-50 to-purple-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-5xl font-bold text-gray-900 mb-6">
            Advanced E-Reader Experience
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Our cutting-edge e-reader brings together the best of digital technology and traditional reading comfort. 
            Discover features that make reading more enjoyable and productive.
          </p>
        </div>
        
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          {/* Features List */}
          <div className="space-y-6">
            {features.map((feature, index) => (
              <div
                key={feature.id}
                className={`p-6 rounded-2xl cursor-pointer transition-all duration-300 ${
                  activeFeature === index
                    ? 'bg-white shadow-lg border-2 border-blue-200'
                    : 'bg-white/50 hover:bg-white/80'
                }`}
                onClick={() => setActiveFeature(index)}
              >
                <div className="flex items-start space-x-4">
                  <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                    activeFeature === index
                      ? 'bg-gradient-to-r from-blue-500 to-purple-500'
                      : 'bg-gray-200'
                  }`}>
                    <i className={`${feature.icon} text-lg ${
                      activeFeature === index ? 'text-white' : 'text-gray-600'
                    }`}></i>
                  </div>
                  <div className="flex-1">
                    <h3 className={`text-lg font-semibold mb-2 ${
                      activeFeature === index ? 'text-blue-600' : 'text-gray-900'
                    }`}>
                      {feature.title}
                    </h3>
                    <p className="text-gray-600 leading-relaxed">
                      {feature.description}
                    </p>
                  </div>
                </div>
              </div>
            ))}
            
            <div className="pt-6">
              <Link
                href="/reading"
                className="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 cursor-pointer whitespace-nowrap"
              >
                <span>Try Our Reader</span>
                <i className="ri-arrow-right-line"></i>
              </Link>
            </div>
          </div>
          
          {/* Feature Image */}
          <div className="relative">
            <div className="bg-white rounded-2xl shadow-2xl overflow-hidden">
              <img
                src={features[activeFeature].image}
                alt={features[activeFeature].title}
                className="w-full h-96 object-cover object-top"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
            </div>
            
            {/* Floating Elements */}
            <div className="absolute -top-4 -right-4 w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg">
              <i className="ri-star-fill text-white text-2xl"></i>
            </div>
            <div className="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
              <i className="ri-bookmark-line text-white text-xl"></i>
            </div>
          </div>
        </div>
        
        {/* Stats Section */}
        <div className="mt-20 bg-white rounded-2xl p-8 shadow-lg">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div className="text-center">
              <div className="text-3xl font-bold text-blue-600 mb-2">99.9%</div>
              <div className="text-gray-600">Uptime</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-purple-600 mb-2">5ms</div>
              <div className="text-gray-600">Page Load</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-green-600 mb-2">∞</div>
              <div className="text-gray-600">Device Sync</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-orange-600 mb-2">24/7</div>
              <div className="text-gray-600">Access</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
