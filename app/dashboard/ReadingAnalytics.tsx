
'use client';

import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';

export default function ReadingAnalytics() {
  const monthlyData = [
    { month: 'Jan', books: 4, hours: 32 },
    { month: 'Feb', books: 3, hours: 28 },
    { month: 'Mar', books: 5, hours: 45 },
    { month: 'Apr', books: 4, hours: 38 },
    { month: 'May', books: 6, hours: 52 },
    { month: 'Jun', books: 5, hours: 41 }
  ];

  const genreData = [
    { name: 'Fiction', value: 35, color: '#3B82F6' },
    { name: 'Non-Fiction', value: 25, color: '#10B981' },
    { name: 'Mystery', value: 20, color: '#F59E0B' },
    { name: 'Romance', value: 12, color: '#EF4444' },
    { name: 'Sci-Fi', value: 8, color: '#8B5CF6' }
  ];

  const stats = [
    { label: 'Total Reading Time', value: '245 hours', icon: 'ri-time-line' },
    { label: 'Average per Day', value: '1.2 hours', icon: 'ri-calendar-line' },
    { label: 'Books This Year', value: '47 books', icon: 'ri-book-line' },
    { label: 'Reading Speed', value: '245 pages/day', icon: 'ri-speed-line' }
  ];

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-4">Reading Analytics</h2>
      
      {/* Stats Grid */}
      <div className="grid grid-cols-2 gap-4 mb-6">
        {stats.map((stat, index) => (
          <div key={index} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
            <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <i className={`${stat.icon} text-blue-600`}></i>
            </div>
            <div>
              <p className="text-sm font-medium text-gray-900">{stat.value}</p>
              <p className="text-xs text-gray-600">{stat.label}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Monthly Reading Chart */}
      <div className="mb-6">
        <h3 className="text-sm font-medium text-gray-700 mb-3">Monthly Progress</h3>
        <div className="h-48">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={monthlyData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="month" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="books" fill="#3B82F6" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Genre Distribution */}
      <div>
        <h3 className="text-sm font-medium text-gray-700 mb-3">Reading by Genre</h3>
        <div className="flex items-center space-x-6">
          <div className="w-32 h-32">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={genreData}
                  cx="50%"
                  cy="50%"
                  innerRadius={30}
                  outerRadius={60}
                  paddingAngle={2}
                  dataKey="value"
                >
                  {genreData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="flex-1 space-y-2">
            {genreData.map((genre, index) => (
              <div key={index} className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <div 
                    className="w-3 h-3 rounded-full"
                    style={{ backgroundColor: genre.color }}
                  ></div>
                  <span className="text-sm text-gray-700">{genre.name}</span>
                </div>
                <span className="text-sm text-gray-600">{genre.value}%</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
