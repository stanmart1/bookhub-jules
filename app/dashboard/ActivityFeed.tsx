
'use client';

import { formatDistanceToNow } from 'date-fns';

export default function ActivityFeed() {
  const activities = [
    {
      type: 'completed',
      title: 'Finished reading "The Alchemist"',
      time: new Date(Date.now() - 2 * 60 * 60 * 1000),
      icon: 'ri-check-line',
      color: 'text-green-600 bg-green-100'
    },
    {
      type: 'review',
      title: 'Wrote review for "Dune"',
      time: new Date(Date.now() - 5 * 60 * 60 * 1000),
      icon: 'ri-star-line',
      color: 'text-yellow-600 bg-yellow-100'
    },
    {
      type: 'started',
      title: 'Started reading "The Psychology of Money"',
      time: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
      icon: 'ri-book-open-line',
      color: 'text-blue-600 bg-blue-100'
    },
    {
      type: 'achievement',
      title: 'Reached 50 books read this year!',
      time: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
      icon: 'ri-trophy-line',
      color: 'text-purple-600 bg-purple-100'
    },
    {
      type: 'purchase',
      title: 'Purchased "Atomic Habits"',
      time: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000),
      icon: 'ri-shopping-cart-line',
      color: 'text-orange-600 bg-orange-100'
    },
    {
      type: 'bookmark',
      title: 'Added "The Midnight Library" to wishlist',
      time: new Date(Date.now() - 4 * 24 * 60 * 60 * 1000),
      icon: 'ri-heart-line',
      color: 'text-red-600 bg-red-100'
    }
  ];

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
      
      <div className="space-y-4">
        {activities.map((activity, index) => (
          <div key={index} className="flex items-start space-x-3">
            <div className={`w-8 h-8 rounded-full flex items-center justify-center ${activity.color}`}>
              <i className={`${activity.icon} text-sm`}></i>
            </div>
            <div className="flex-1">
              <p className="text-sm text-gray-900">{activity.title}</p>
              <p className="text-xs text-gray-500 mt-1">
                {formatDistanceToNow(activity.time, { addSuffix: true })}
              </p>
            </div>
          </div>
        ))}
      </div>
      
      <button className="w-full mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer whitespace-nowrap">
        View All Activity
      </button>
    </div>
  );
}
