
'use client';

import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from 'recharts';

export default function ReadingProgress() {
  const weeklyData = [
    { day: 'Mon', pages: 45, hours: 1.5 },
    { day: 'Tue', pages: 32, hours: 1.2 },
    { day: 'Wed', pages: 28, hours: 1.0 },
    { day: 'Thu', pages: 52, hours: 2.1 },
    { day: 'Fri', pages: 38, hours: 1.4 },
    { day: 'Sat', pages: 65, hours: 2.8 },
    { day: 'Sun', pages: 41, hours: 1.7 }
  ];

  const currentBooks = [
    {
      title: 'The Psychology of Money',
      author: 'Morgan Housel',
      progress: 75,
      cover: 'https://readdy.ai/api/search-image?query=psychology%20of%20money%20book%20cover%20design%2C%20financial%20concepts%2C%20modern%20minimalist%20style%2C%20professional%20layout%2C%20clean%20typography&width=80&height=120&seq=book-1&orientation=portrait'
    },
    {
      title: 'Atomic Habits',
      author: 'James Clear',
      progress: 45,
      cover: 'https://readdy.ai/api/search-image?query=atomic%20habits%20book%20cover%20design%2C%20habit%20formation%20concept%2C%20scientific%20approach%2C%20modern%20design%2C%20motivational%20theme&width=80&height=120&seq=book-2&orientation=portrait'
    },
    {
      title: 'The Midnight Library',
      author: 'Matt Haig',
      progress: 92,
      cover: 'https://readdy.ai/api/search-image?query=midnight%20library%20book%20cover%20design%2C%20mystical%20library%20setting%2C%20dark%20blue%20tones%2C%20magical%20atmosphere%2C%20literary%20fiction%20style&width=80&height=120&seq=book-3&orientation=portrait'
    }
  ];

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-4">Reading Progress</h2>
      
      {/* Weekly Chart */}
      <div className="mb-6">
        <h3 className="text-sm font-medium text-gray-700 mb-3">This Week's Activity</h3>
        <div className="h-64">
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={weeklyData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="day" />
              <YAxis />
              <Tooltip />
              <Area type="monotone" dataKey="pages" stroke="#3B82F6" fill="#3B82F6" fillOpacity={0.6} />
            </AreaChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Current Books */}
      <div>
        <h3 className="text-sm font-medium text-gray-700 mb-3">Currently Reading</h3>
        <div className="space-y-4">
          {currentBooks.map((book, index) => (
            <div key={index} className="flex items-center space-x-3">
              <img 
                src={book.cover} 
                alt={book.title}
                className="w-12 h-18 object-cover object-top rounded"
              />
              <div className="flex-1">
                <h4 className="font-medium text-sm text-gray-900">{book.title}</h4>
                <p className="text-xs text-gray-600">{book.author}</p>
                <div className="mt-2 flex items-center space-x-2">
                  <div className="flex-1 bg-gray-200 rounded-full h-2">
                    <div 
                      className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                      style={{ width: `${book.progress}%` }}
                    ></div>
                  </div>
                  <span className="text-xs text-gray-500">{book.progress}%</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
