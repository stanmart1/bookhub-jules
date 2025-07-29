
'use client';

import { useEffect, useState } from 'react';
import { useApi, OrderNotification } from '../../lib/api';
import { formatDistanceToNow } from 'date-fns';

export default function NotificationCenter() {
  const { getOrderNotifications, markOrderNotificationAsRead } = useApi();
  const [notifications, setNotifications] = useState<OrderNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    getOrderNotifications()
      .then((res) => {
        setNotifications(res.data || []);
        setError(null);
      })
      .catch((err) => {
        setError('Failed to load notifications');
      })
      .finally(() => setLoading(false));
  }, []);

  const handleMarkAsRead = async (id: number) => {
    await markOrderNotificationAsRead(id);
    setNotifications((prev) => prev.map((n) => n.id === id ? { ...n, read_at: new Date().toISOString() } : n));
  };

  const unreadCount = notifications.filter(n => !n.read_at).length;

  if (loading) {
    return <div className="p-6 text-center text-gray-500">Loading notifications...</div>;
  }
  if (error) {
    return <div className="p-6 text-center text-red-500">{error}</div>;
  }
  if (!notifications.length) {
    return <div className="p-6 text-center text-gray-500">No notifications found.</div>;
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between mb-4">
        <h2 className="text-lg font-semibold text-gray-900">Order Notifications</h2>
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
            className={`flex items-start space-x-3 p-3 rounded-lg transition-colors cursor-pointer ${!notification.read_at ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200'}`}
            onClick={() => handleMarkAsRead(notification.id)}
          >
            <div className="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-blue-100 text-blue-600">
              <i className="ri-notification-3-line text-sm"></i>
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="font-medium text-sm text-gray-900 mb-1">
                {notification.title}
              </h3>
              <p className="text-xs text-gray-600 mb-1 line-clamp-2">
                {notification.message}
              </p>
              <p className="text-xs text-gray-500">
                {formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}
              </p>
            </div>
            {!notification.read_at && (
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
