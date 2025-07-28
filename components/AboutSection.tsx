
'use client';

export default function AboutSection() {
  const features = [
    {
      icon: 'ri-book-open-line',
      title: 'Vast Library',
      description: 'Access over 50,000 books across all genres, from bestsellers to hidden gems.',
      color: 'from-blue-500 to-cyan-500'
    },
    {
      icon: 'ri-smartphone-line',
      title: 'Multi-Device Sync',
      description: 'Read seamlessly across all your devices with automatic sync and backup.',
      color: 'from-purple-500 to-pink-500'
    },
    {
      icon: 'ri-user-heart-line',
      title: 'Personalized Experience',
      description: 'Get tailored recommendations based on your reading history and preferences.',
      color: 'from-green-500 to-teal-500'
    },
    {
      icon: 'ri-bar-chart-line',
      title: 'Reading Analytics',
      description: 'Track your progress, set goals, and discover insights about your reading habits.',
      color: 'from-yellow-500 to-orange-500'
    },
    {
      icon: 'ri-group-line',
      title: 'Community Features',
      description: 'Connect with fellow readers, share reviews, and join book discussions.',
      color: 'from-indigo-500 to-purple-500'
    },
    {
      icon: 'ri-cloud-line',
      title: 'Cloud Storage',
      description: 'Your entire library is safely stored in the cloud and accessible anywhere.',
      color: 'from-pink-500 to-red-500'
    }
  ];

  return (
    <div className="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-5xl font-bold text-gray-900 mb-6">
            Why Choose Our Platform?
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            We've built the ultimate reading platform that combines the best of digital technology 
            with the timeless joy of reading. Discover what makes us different.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, index) => (
            <div 
              key={index}
              className="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100"
            >
              <div className={`w-16 h-16 bg-gradient-to-r ${feature.color} rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300`}>
                <i className={`${feature.icon} text-2xl text-white`}></i>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-3">
                {feature.title}
              </h3>
              <p className="text-gray-600 leading-relaxed">
                {feature.description}
              </p>
            </div>
          ))}
        </div>
        
        <div className="mt-16 text-center">
          <div className="bg-white rounded-2xl p-8 shadow-lg inline-block">
            <div className="flex items-center justify-center space-x-8 mb-6">
              <div className="text-center">
                <div className="text-3xl font-bold text-blue-600 mb-1">50K+</div>
                <div className="text-gray-600">Books</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-purple-600 mb-1">500K+</div>
                <div className="text-gray-600">Active Users</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-green-600 mb-1">1M+</div>
                <div className="text-gray-600">Books Read</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-orange-600 mb-1">4.9/5</div>
                <div className="text-gray-600">User Rating</div>
              </div>
            </div>
            <p className="text-gray-600">
              Join thousands of readers who have made us their go-to reading platform
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
