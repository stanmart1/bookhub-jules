
'use client';

import { useEffect, useState } from 'react';
import { useApi, Order } from '../../lib/api';
import { formatDistanceToNow } from 'date-fns';

export default function PurchaseHistory() {
  const { getOrders, getOrderReceipt, cancelOrder } = useApi();
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [modalOpen, setModalOpen] = useState(false);
  const [modalLoading, setModalLoading] = useState(false);
  const [cancelError, setCancelError] = useState<string | null>(null);
  const [cancelSuccess, setCancelSuccess] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    getOrders()
      .then((res) => {
        setOrders(res.data || []);
        setError(null);
      })
      .catch((err) => {
        setError('Failed to load orders');
      })
      .finally(() => setLoading(false));
  }, []);

  const handleViewDetails = (order: Order) => {
    setSelectedOrder(order);
    setModalOpen(true);
    setCancelError(null);
    setCancelSuccess(null);
  };

  const handleCloseModal = () => {
    setModalOpen(false);
    setSelectedOrder(null);
    setCancelError(null);
    setCancelSuccess(null);
  };

  const handleViewReceipt = async (orderId: number) => {
    setModalLoading(true);
    try {
      const res = await getOrderReceipt(orderId);
      if (res.success && res.data && res.data.download_url) {
        window.open(res.data.download_url, '_blank');
      } else {
        alert('Receipt not available');
      }
    } finally {
      setModalLoading(false);
    }
  };

  const handleCancelOrder = async (orderId: number) => {
    setModalLoading(true);
    setCancelError(null);
    setCancelSuccess(null);
    try {
      const res = await cancelOrder(orderId);
      if (res.success) {
        setCancelSuccess('Order cancelled successfully.');
        // Refresh orders
        const refreshed = await getOrders();
        setOrders(refreshed.data || []);
        setSelectedOrder(refreshed.data?.find((o) => o.id === orderId) || null);
      } else {
        setCancelError(res.message || 'Failed to cancel order.');
      }
    } catch (e) {
      setCancelError('Failed to cancel order.');
    } finally {
      setModalLoading(false);
    }
  };

  if (loading) {
    return <div className="p-6 text-center text-gray-500">Loading purchase history...</div>;
  }
  if (error) {
    return <div className="p-6 text-center text-red-500">{error}</div>;
  }
  if (!orders.length) {
    return <div className="p-6 text-center text-gray-500">No purchases found.</div>;
  }

  return (
    <div className="space-y-4">
      {orders.map((order) => (
        <div key={order.id} className="border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50" onClick={() => handleViewDetails(order)}>
          <div className="flex items-center justify-between mb-3">
            <div>
              <h3 className="font-medium text-gray-900">Order {order.order_number}</h3>
              <p className="text-sm text-gray-500">
                {formatDistanceToNow(new Date(order.created_at), { addSuffix: true })}
              </p>
            </div>
            <div className="text-right">
              <p className="font-semibold text-gray-900">{order.currency} {order.total_amount.toFixed(2)}</p>
              <span className={`inline-block px-2 py-1 text-xs rounded ${order.status === 'completed' ? 'bg-green-100 text-green-800' : order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : order.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'}`}>
                {order.status}
              </span>
            </div>
          </div>
          <div className="space-y-2">
            {order.items.map((item) => (
              <div key={item.id} className="flex items-center space-x-3">
                {item.cover_image && (
                  <img src={item.cover_image} alt={item.title} className="w-12 h-18 object-cover object-top rounded" />
                )}
                <div className="flex-1">
                  <h4 className="font-medium text-sm text-gray-900">{item.title}</h4>
                  <p className="text-xs text-gray-600">{item.author}</p>
                </div>
                <p className="text-sm font-medium text-gray-900">{order.currency} {item.price.toFixed(2)}</p>
              </div>
            ))}
          </div>
        </div>
      ))}

      {/* Order Details Modal */}
      {modalOpen && selectedOrder && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
          <div className="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
            <button className="absolute top-2 right-2 text-gray-400 hover:text-gray-600" onClick={handleCloseModal}>&times;</button>
            <h2 className="text-lg font-semibold mb-2">Order Details</h2>
            <div className="mb-2">
              <span className="font-medium">Order Number:</span> {selectedOrder.order_number}
            </div>
            <div className="mb-2">
              <span className="font-medium">Status:</span> <span className={`inline-block px-2 py-1 text-xs rounded ${selectedOrder.status === 'completed' ? 'bg-green-100 text-green-800' : selectedOrder.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : selectedOrder.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'}`}>{selectedOrder.status}</span>
            </div>
            <div className="mb-2">
              <span className="font-medium">Total:</span> {selectedOrder.currency} {selectedOrder.total_amount.toFixed(2)}
            </div>
            <div className="mb-2">
              <span className="font-medium">Created:</span> {formatDistanceToNow(new Date(selectedOrder.created_at), { addSuffix: true })}
            </div>
            <div className="mb-4">
              <span className="font-medium">Items:</span>
              <ul className="list-disc ml-6 mt-1">
                {selectedOrder.items.map((item) => (
                  <li key={item.id} className="mb-1">
                    <span className="font-medium">{item.title}</span> by {item.author} ({selectedOrder.currency} {item.price.toFixed(2)})
                  </li>
                ))}
              </ul>
            </div>
            <div className="flex items-center space-x-4">
              {selectedOrder.status === 'completed' && (
                <button
                  onClick={() => handleViewReceipt(selectedOrder.id)}
                  className="text-blue-600 hover:underline text-sm font-medium disabled:opacity-50"
                  disabled={modalLoading}
                >
                  {modalLoading ? 'Loading...' : 'View Receipt'}
                </button>
              )}
              {(selectedOrder.status === 'pending' || selectedOrder.status === 'processing') && (
                <button
                  onClick={() => handleCancelOrder(selectedOrder.id)}
                  className="text-red-600 hover:underline text-sm font-medium disabled:opacity-50"
                  disabled={modalLoading}
                >
                  {modalLoading ? 'Cancelling...' : 'Cancel Order'}
                </button>
              )}
            </div>
            {cancelError && <div className="mt-2 text-red-500 text-sm">{cancelError}</div>}
            {cancelSuccess && <div className="mt-2 text-green-600 text-sm">{cancelSuccess}</div>}
          </div>
        </div>
      )}
    </div>
  );
}
