
'use client';

import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';

export default function OverviewStats() {
  const stats = [
    {
      title: 'Total Users',
      value: '12,847',
      change: '+12.3%',
      changeType: 'positive',
      icon: 'ri-user-line',
      color: 'bg-blue-500'
    },
    {
      title: 'Total Books',
      value: '3,429',
      change: '+8.1%',
      changeType: 'positive',
      icon: 'ri-book-line',
      color: 'bg-green-500'
    },
    {
      title: 'Monthly Sales',
      value: '$45,678',
      change: '+23.4%',
      changeType: 'positive',
      icon: 'ri-money-dollar-circle-line',
      color: 'bg-purple-500'
    },
    {
      title: 'Active Reviews',
      value: '1,829',
      change: '+5.7%',
      changeType: 'positive',
      icon: 'ri-star-line',
      color: 'bg-yellow-500'
    }
  ];

  const trendData = [
    { date: 'Jan', users: 8500, sales: 32000, reviews: 1200 },
    { date: 'Feb', users: 9200, sales: 36000, reviews: 1350 },
    { date: 'Mar', users: 10100, sales: 38000, reviews: 1450 },
    { date: 'Apr', users: 11200, sales: 42000, reviews: 1650 },
    { date: 'May', users: 12100, sales: 44000, reviews: 1750 },
    { date: 'Jun', users: 12847, sales: 45678, reviews: 1829 }
  ];

  const dailyActivity = [
    { day: 'Mon', active: 2847, orders: 89 },
    { day: 'Tue', active: 3124, orders: 92 },
    { day: 'Wed', active: 2956, orders: 78 },
    { day: 'Thu', active: 3287, orders: 105 },
    { day: 'Fri', active: 3891, orders: 134 },
    { day: 'Sat', active: 4234, orders: 156 },
    { day: 'Sun', active: 3642, orders: 112 }
  ];

  return (
    <div className="space-y-8">
      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, index) => (
          <div key={index} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center">
              <div className={`w-12 h-12 ${stat.color} rounded-lg flex items-center justify-center`}>
                <i className={`${stat.icon} text-white text-xl`}></i>
              </div>
              <div className="ml-4">
                <p className="text-sm text-gray-600">{stat.title}</p>
                <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
                <p className={`text-sm ${stat.changeType === 'positive' ? 'text-green-600' : 'text-red-600'}`}>
                  {stat.change} from last month
                </p>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Trend Chart */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Growth Trends</h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={trendData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="date" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="users" stroke="#3B82F6" strokeWidth={2} />
                <Line type="monotone" dataKey="sales" stroke="#10B981" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Daily Activity */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Daily Activity</h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={dailyActivity}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="day" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="active" fill="#3B82F6" />
                <Bar dataKey="orders" fill="#10B981" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      {/* Recent Activities */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
        <div className="space-y-4">
          {[
            { action: 'New user registered', user: 'john.doe@email.com', time: '2 minutes ago', type: 'user' },
            { action: 'Book published', book: 'The Art of Programming', time: '15 minutes ago', type: 'book' },
            { action: 'Large order placed', amount: '$234.50', time: '1 hour ago', type: 'order' },
            { action: 'Review flagged', review: 'Review ID #1234', time: '2 hours ago', type: 'review' },
            { action: 'New author joined', author: 'Sarah Johnson', time: '3 hours ago', type: 'author' }
          ].map((activity, index) => (
            <div key={index} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center ${
                activity.type === 'user' ? 'bg-blue-100 text-blue-600' :
                activity.type === 'book' ? 'bg-green-100 text-green-600' :
                activity.type === 'order' ? 'bg-purple-100 text-purple-600' :
                activity.type === 'review' ? 'bg-yellow-100 text-yellow-600' :
                'bg-gray-100 text-gray-600'
              }`}>
                <i className={`${
                  activity.type === 'user' ? 'ri-user-add-line' :
                  activity.type === 'book' ? 'ri-book-line' :
                  activity.type === 'order' ? 'ri-shopping-cart-line' :
                  activity.type === 'review' ? 'ri-flag-line' :
                  'ri-user-line'
                } text-sm`}></i>
              </div>
              <div className="flex-1">
                <p className="text-sm font-medium text-gray-900">{activity.action}</p>
                <p className="text-xs text-gray-600">
                  {activity.user || activity.book || activity.amount || activity.review || activity.author}
                </p>
              </div>
              <span className="text-xs text-gray-500">{activity.time}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
