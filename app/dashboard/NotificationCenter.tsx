
'use client';

import { useState } from 'react';
import { formatDistanceToNow } from 'date-fns';

export default function NotificationCenter() {
  const [notifications, setNotifications] = useState([
    {
      id: 1,
      type: 'achievement',
      title: 'New Achievement Unlocked!',
      message: 'You reached your monthly reading goal',
      time: new Date(Date.now() - 30 * 60 * 1000),
      read: false,
      icon: 'ri-trophy-line',
      color: 'text-yellow-600 bg-yellow-100'
    },
    {
      id: 2,
      type: 'book',
      title: 'New Book Recommendation',
      message: 'Based on your reading history, we recommend "The Seven Husbands of Evelyn Hugo"',
      time: new Date(Date.now() - 2 * 60 * 60 * 1000),
      read: false,
      icon: 'ri-book-line',
      color: 'text-blue-600 bg-blue-100'
    },
    {
      id: 3,
      type: 'social',
      title: 'Review Liked',
      message: 'Someone found your review of "Atomic Habits" helpful',
      time: new Date(Date.now() - 4 * 60 * 60 * 1000),
      read: true,
      icon: 'ri-thumb-up-line',
      color: 'text-green-600 bg-green-100'
    },
    {
      id: 4,
      type: 'reminder',
      title: 'Reading Reminder',
      message: 'You haven\'t read today. Continue with "The Psychology of Money"',
      time: new Date(Date.now() - 6 * 60 * 60 * 1000),
      read: true,
      icon: 'ri-alarm-line',
      color: 'text-purple-600 bg-purple-100'
    }
  ]);

  const markAsRead = (id: number) => {
    setNotifications(notifications.map(notif => 
      notif.id === id ? { ...notif, read: true } : notif
    ));
  };

  const unreadCount = notifications.filter(n => !n.read).length;

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between mb-4">
        <h2 className="text-lg font-semibold text-gray-900">Notifications</h2>
        {unreadCount > 0 && (
          <span className="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
            {unreadCount}
          </span>
        )}
      </div>
      
      <div className="space-y-3">
        {notifications.map((notification) => (
          <div 
            key={notification.id}
            className={`flex items-start space-x-3 p-3 rounded-lg transition-colors cursor-pointer ${
              !notification.read 
                ? 'bg-blue-50 border border-blue-200' 
                : 'bg-gray-50 border border-gray-200'
            }`}
            onClick={() => markAsRead(notification.id)}
          >
            <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ${notification.color}`}>
              <i className={`${notification.icon} text-sm`}></i>
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="font-medium text-sm text-gray-900 mb-1">
                {notification.title}
              </h3>
              <p className="text-xs text-gray-600 mb-1 line-clamp-2">
                {notification.message}
              </p>
              <p className="text-xs text-gray-500">
                {formatDistanceToNow(notification.time, { addSuffix: true })}
              </p>
            </div>
            {!notification.read && (
              <div className="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>
            )}
          </div>
        ))}
      </div>
      
      <button className="w-full mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer whitespace-nowrap">
        View All Notifications
      </button>
    </div>
  );
}
