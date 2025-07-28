
'use client';

import Link from 'next/link';

export default function HeroSection() {
  return (
    <div className="relative min-h-screen flex items-center overflow-hidden">
      {/* Background */}
      <div 
        className="absolute inset-0 bg-cover bg-center"
        style={{
          backgroundImage: `url('https://readdy.ai/api/search-image?query=modern%20digital%20library%20with%20floating%20books%2C%20futuristic%20reading%20environment%2C%20holographic%20displays%2C%20blue%20and%20purple%20gradient%20lighting%2C%20people%20using%20digital%20devices%20to%20read%2C%20contemporary%20minimalist%20design%2C%20high-tech%20atmosphere%2C%20inspiring%20educational%20technology&width=1920&height=1080&seq=hero-modern&orientation=landscape')`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-blue-900/90 via-purple-900/80 to-indigo-900/90"></div>
      </div>
      
      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div className="text-center">
          <h1 className="text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            Your Digital
            <span className="block text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-400">
              Reading Universe
            </span>
          </h1>
          <p className="text-xl lg:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed">
            Discover, read, and manage your entire book collection in one beautiful platform. 
            From ebooks to audiobooks, tracking to recommendations - everything you need for the perfect reading experience.
          </p>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            <Link 
              href="/dashboard"
              className="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 cursor-pointer text-center whitespace-nowrap shadow-lg"
            >
              Start Reading Free
            </Link>
            <Link 
              href="/reading"
              className="border-2 border-white text-white px-8 py-4 rounded-full font-semibold hover:bg-white hover:text-blue-600 transition-all duration-300 cursor-pointer text-center whitespace-nowrap"
            >
              Try Our Reader
            </Link>
          </div>
          
          <div className="flex flex-wrap justify-center gap-8 text-blue-100">
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full flex items-center justify-center">
                <i className="ri-book-line text-white text-sm"></i>
              </div>
              <span className="text-lg">50,000+ Books</span>
            </div>
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                <i className="ri-user-line text-white text-sm"></i>
              </div>
              <span className="text-lg">500K+ Readers</span>
            </div>
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                <i className="ri-star-line text-white text-sm"></i>
              </div>
              <span className="text-lg">4.9/5 Rating</span>
            </div>
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gradient-to-r from-green-400 to-teal-500 rounded-full flex items-center justify-center">
                <i className="ri-smartphone-line text-white text-sm"></i>
              </div>
              <span className="text-lg">Multi-Device</span>
            </div>
          </div>
        </div>
      </div>
      
      {/* Floating Elements */}
      <div className="absolute top-20 left-10 w-20 h-20 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full opacity-20 animate-pulse"></div>
      <div className="absolute bottom-32 right-16 w-16 h-16 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full opacity-20 animate-pulse"></div>
      <div className="absolute top-1/3 right-20 w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full opacity-20 animate-pulse"></div>
    </div>
  );
}
