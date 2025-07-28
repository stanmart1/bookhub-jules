
'use client';

import Link from 'next/link';

export default function BlogSection() {
  const blogPosts = [
    {
      id: '1',
      title: 'The Future of Digital Reading: Trends to Watch in 2024',
      excerpt: 'Explore emerging technologies and trends that are shaping the future of how we read and interact with digital content.',
      author: 'Sarah Johnson',
      date: 'March 15, 2024',
      readTime: '8 min read',
      category: 'Technology',
      image: 'https://readdy.ai/api/search-image?query=futuristic%20digital%20reading%20technology%2C%20holographic%20books%2C%20augmented%20reality%20reading%2C%20advanced%20e-reader%20devices%2C%20modern%20library%20technology&width=400&height=250&seq=blog-1&orientation=landscape'
    },
    {
      id: '2',
      title: 'Building Better Reading Habits: A Science-Based Approach',
      excerpt: 'Discover evidence-based strategies to develop consistent reading habits and maximize your learning from books.',
      author: 'Dr. Michael Chen',
      date: 'March 12, 2024',
      readTime: '6 min read',
      category: 'Self-Improvement',
      image: 'https://readdy.ai/api/search-image?query=reading%20habits%20formation%2C%20daily%20reading%20routine%2C%20book%20study%20methods%2C%20productive%20reading%20environment%2C%20learning%20optimization&width=400&height=250&seq=blog-2&orientation=landscape'
    },
    {
      id: '3',
      title: 'Author Spotlight: Rising Stars in Contemporary Fiction',
      excerpt: 'Meet the emerging voices in contemporary fiction who are redefining storytelling for the modern reader.',
      author: 'Emma Rodriguez',
      date: 'March 10, 2024',
      readTime: '10 min read',
      category: 'Literature',
      image: 'https://readdy.ai/api/search-image?query=contemporary%20fiction%20authors%2C%20modern%20literature%2C%20emerging%20writers%2C%20diverse%20storytelling%2C%20literary%20creativity&width=400&height=250&seq=blog-3&orientation=landscape'
    },
    {
      id: '4',
      title: 'The Psychology of Reading: Why We Love Stories',
      excerpt: 'Uncover the psychological mechanisms behind our love for stories and how reading affects our brain and emotions.',
      author: 'Prof. David Wilson',
      date: 'March 8, 2024',
      readTime: '12 min read',
      category: 'Psychology',
      image: 'https://readdy.ai/api/search-image?query=psychology%20of%20reading%2C%20brain%20and%20books%2C%20storytelling%20psychology%2C%20emotional%20connection%20to%20stories%2C%20reading%20and%20mental%20health&width=400&height=250&seq=blog-4&orientation=landscape'
    },
    {
      id: '5',
      title: 'Book Recommendations: Hidden Gems of 2024',
      excerpt: 'Discover overlooked masterpieces and hidden gems that deserve a spot on your reading list this year.',
      author: 'Lisa Thompson',
      date: 'March 5, 2024',
      readTime: '7 min read',
      category: 'Reviews',
      image: 'https://readdy.ai/api/search-image?query=hidden%20gem%20books%2C%20undiscovered%20literature%2C%20book%20recommendations%2C%20diverse%20book%20collection%2C%20literary%20treasures&width=400&height=250&seq=blog-5&orientation=landscape'
    },
    {
      id: '6',
      title: 'Digital vs. Physical Books: Finding Your Perfect Balance',
      excerpt: 'Explore the pros and cons of digital and physical reading formats and how to create the ideal reading experience.',
      author: 'Mark Anderson',
      date: 'March 3, 2024',
      readTime: '9 min read',
      category: 'Reading Tips',
      image: 'https://readdy.ai/api/search-image?query=digital%20books%20vs%20physical%20books%2C%20e-reader%20and%20printed%20books%20comparison%2C%20reading%20formats%2C%20modern%20reading%20choices&width=400&height=250&seq=blog-6&orientation=landscape'
    }
  ];

  return (
    <div className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-5xl font-bold text-gray-900 mb-6">
            Reading Insights & Stories
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Stay updated with the latest trends, tips, and insights from the world of reading. 
            Our blog covers everything from book recommendations to reading psychology.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {blogPosts.map((post) => (
            <Link key={post.id} href={`/blog/${post.id}`} className="group cursor-pointer">
              <article className="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div className="relative">
                  <img
                    src={post.image}
                    alt={post.title}
                    className="w-full h-48 object-cover object-top"
                  />
                  <div className="absolute top-4 left-4 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {post.category}
                  </div>
                </div>
                
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                    {post.title}
                  </h3>
                  <p className="text-gray-600 mb-4 line-clamp-3">
                    {post.excerpt}
                  </p>
                  
                  <div className="flex items-center justify-between text-sm text-gray-500 mb-4">
                    <div className="flex items-center space-x-2">
                      <i className="ri-user-line"></i>
                      <span>{post.author}</span>
                    </div>
                    <div className="flex items-center space-x-2">
                      <i className="ri-time-line"></i>
                      <span>{post.readTime}</span>
                    </div>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-500">{post.date}</span>
                    <span className="text-blue-600 font-medium group-hover:text-blue-700 transition-colors">
                      Read More →
                    </span>
                  </div>
                </div>
              </article>
            </Link>
          ))}
        </div>
        
        <div className="text-center mt-12">
          <Link
            href="/blog"
            className="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 cursor-pointer whitespace-nowrap"
          >
            <span>View All Articles</span>
            <i className="ri-arrow-right-line"></i>
          </Link>
        </div>
      </div>
    </div>
  );
}
