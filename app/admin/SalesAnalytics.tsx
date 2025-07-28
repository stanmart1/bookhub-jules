
'use client';

import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, PieChart, Pie, Cell } from 'recharts';

export default function SalesAnalytics() {
  const salesData = [
    { month: 'Jan', sales: 32000, orders: 456, avgOrder: 70 },
    { month: 'Feb', sales: 36000, orders: 512, avgOrder: 72 },
    { month: 'Mar', sales: 38000, orders: 523, avgOrder: 73 },
    { month: 'Apr', sales: 42000, orders: 578, avgOrder: 75 },
    { month: 'May', sales: 44000, orders: 612, avgOrder: 72 },
    { month: 'Jun', sales: 45678, orders: 634, avgOrder: 74 }
  ];

  const topBooks = [
    { title: 'Atomic Habits', sales: 2156, revenue: 36648, growth: 23.4 },
    { title: 'The Psychology of Money', sales: 1247, revenue: 24940, growth: 18.2 },
    { title: 'Dune', sales: 987, revenue: 18753, growth: 15.1 },
    { title: 'The Midnight Library', sales: 876, revenue: 13140, growth: 12.8 },
    { title: 'Where the Crawdads Sing', sales: 743, revenue: 11873, growth: 9.6 }
  ];

  const categoryData = [
    { name: 'Fiction', value: 35, sales: 15234, color: '#3B82F6' },
    { name: 'Non-Fiction', value: 25, sales: 10876, color: '#10B981' },
    { name: 'Self-Help', value: 20, sales: 8765, color: '#F59E0B' },
    { name: 'Mystery', value: 12, sales: 5432, color: '#EF4444' },
    { name: 'Romance', value: 8, sales: 3456, color: '#8B5CF6' }
  ];

  const orders = [
    {
      id: 'ORD-2024-456',
      customer: 'Sarah Johnson',
      items: 2,
      total: 34.98,
      date: '2024-06-15',
      status: 'completed'
    },
    {
      id: 'ORD-2024-457',
      customer: 'Michael Chen',
      items: 1,
      total: 19.99,
      date: '2024-06-15',
      status: 'processing'
    },
    {
      id: 'ORD-2024-458',
      customer: 'Emily Rodriguez',
      items: 3,
      total: 52.97,
      date: '2024-06-14',
      status: 'completed'
    },
    {
      id: 'ORD-2024-459',
      customer: 'David Williams',
      items: 1,
      total: 16.99,
      date: '2024-06-14',
      status: 'shipped'
    },
    {
      id: 'ORD-2024-460',
      customer: 'Lisa Thompson',
      items: 2,
      total: 29.98,
      date: '2024-06-13',
      status: 'completed'
    }
  ];

  return (
    <div className="space-y-8">
      {/* Sales Overview */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600">Total Revenue</p>
              <p className="text-2xl font-bold text-gray-900">$45,678</p>
              <p className="text-sm text-green-600">+12.5% from last month</p>
            </div>
            <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <i className="ri-money-dollar-circle-line text-green-600 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600">Total Orders</p>
              <p className="text-2xl font-bold text-gray-900">634</p>
              <p className="text-sm text-blue-600">+8.3% from last month</p>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <i className="ri-shopping-cart-line text-blue-600 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600">Average Order</p>
              <p className="text-2xl font-bold text-gray-900">$74</p>
              <p className="text-sm text-purple-600">+2.1% from last month</p>
            </div>
            <div className="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <i className="ri-bar-chart-line text-purple-600 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Sales Trend */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Sales Trend</h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={salesData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="sales" stroke="#3B82F6" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Category Sales */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Sales by Category</h3>
          <div className="flex items-center space-x-6">
            <div className="w-32 h-32">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={categoryData}
                    cx="50%"
                    cy="50%"
                    outerRadius={60}
                    dataKey="value"
                  >
                    {categoryData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="flex-1 space-y-2">
              {categoryData.map((category, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <div 
                      className="w-3 h-3 rounded-full"
                      style={{ backgroundColor: category.color }}
                    ></div>
                    <span className="text-sm text-gray-700">{category.name}</span>
                  </div>
                  <span className="text-sm text-gray-600">${category.sales}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Top Selling Books */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Top Selling Books</h3>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Growth</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {topBooks.map((book, index) => (
                <tr key={index} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{book.title}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{book.sales}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">${book.revenue}</td>
                  <td className="px-6 py-4 text-sm text-green-600">+{book.growth}%</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Recent Orders */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Orders</h3>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {orders.map((order) => (
                <tr key={order.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{order.id}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{order.customer}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{order.items}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">${order.total}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{order.date}</td>
                  <td className="px-6 py-4">
                    <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                      order.status === 'completed' ? 'bg-green-100 text-green-800' :
                      order.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                      order.status === 'shipped' ? 'bg-purple-100 text-purple-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {order.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm">
                    <div className="flex space-x-2">
                      <button className="text-blue-600 hover:text-blue-800 cursor-pointer">
                        <i className="ri-eye-line"></i>
                      </button>
                      <button className="text-green-600 hover:text-green-800 cursor-pointer">
                        <i className="ri-edit-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
