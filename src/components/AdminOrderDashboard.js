/**
 * Admin Order Management Dashboard
 * 
 * This component displays:
 * - Real-time notification badge when new orders arrive
 * - List of pending orders from mobile
 * - Order details with action buttons
 * - Order status tracking
 * 
 * Integration with NotificationService for real-time updates
 */

import React, { useState, useEffect } from 'react';

const AdminOrderDashboard = () => {
  const [orders, setOrders] = useState([]);
  const [newOrderCount, setNewOrderCount] = useState(0);
  const [showNotification, setShowNotification] = useState(false);
  const [notificationMessage, setNotificationMessage] = useState('');
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('pending_confirmation');

  // Fetch initial orders from backend
  useEffect(() => {
    fetchOrders();
  }, []);

  // Listen for new order notifications
  useEffect(() => {
    // This would be implemented on the web admin side
    // Subscribe to real-time order events
    const unsubscribe = subscribeToOrderNotifications();
    return unsubscribe;
  }, []);

  const fetchOrders = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/v1/admin/orders?status=pending_confirmation', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('adminToken')}`,
        },
      });
      const data = await response.json();
      setOrders(data.data || []);
    } catch (error) {
      console.error('Error fetching orders:', error);
    } finally {
      setLoading(false);
    }
  };

  const subscribeToOrderNotifications = () => {
    // This function would connect to the real-time notification service
    // It could use WebSockets, Server-Sent Events (SSE), or polling
    
    // Example implementation (would need backend support):
    const handleNewOrder = (orderData) => {
      console.log('🔔 New order received:', orderData);
      
      // Add order to list
      setOrders(prev => [orderData.order, ...prev]);
      
      // Show notification
      setNewOrderCount(prev => prev + 1);
      setNotificationMessage(`New order #${orderData.order.orderRef} from ${orderData.order.customerName}`);
      setShowNotification(true);
      
      // Auto-hide notification after 5 seconds
      setTimeout(() => setShowNotification(false), 5000);
      
      // Play notification sound
      playNotificationSound();
    };

    // In a real app, you would:
    // 1. Connect to WebSocket: const ws = new WebSocket(...)
    // 2. or use Server-Sent Events: const eventSource = new EventSource(...)
    // 3. or setup polling with setInterval

    // For now, return empty unsubscribe function
    return () => {};
  };

  const playNotificationSound = () => {
    try {
      const audio = new Audio('/notification-sound.mp3');
      audio.play();
    } catch (error) {
      console.warn('Could not play notification sound:', error);
    }
  };

  const handleConfirmOrder = async (orderId) => {
    try {
      const response = await fetch(`/api/v1/admin/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('adminToken')}`,
        },
        body: JSON.stringify({
          status: 'confirmed',
          notes: 'Order confirmed by admin',
        }),
      });

      if (response.ok) {
        // Update local state
        setOrders(prev => prev.map(order => 
          order.id === orderId ? { ...order, status: 'confirmed' } : order
        ));
        alert('Order confirmed successfully!');
      }
    } catch (error) {
      console.error('Error confirming order:', error);
      alert('Failed to confirm order');
    }
  };

  const handleMarkAsProcessing = async (orderId) => {
    try {
      const response = await fetch(`/api/v1/admin/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('adminToken')}`,
        },
        body: JSON.stringify({
          status: 'processing',
          notes: 'Order is being processed',
        }),
      });

      if (response.ok) {
        setOrders(prev => prev.map(order => 
          order.id === orderId ? { ...order, status: 'processing' } : order
        ));
        alert('Order marked as processing!');
      }
    } catch (error) {
      console.error('Error updating order:', error);
    }
  };

  return (
    <div style={styles.container}>
      {/* Notification Alert */}
      {showNotification && (
        <div style={styles.notificationBanner}>
          <span style={styles.notificationBell}>🔔</span>
          <div style={styles.notificationContent}>
            <strong>{notificationMessage}</strong>
            <br />
            <small>You have {newOrderCount} new order{newOrderCount !== 1 ? 's' : ''}</small>
          </div>
          <button 
            onClick={() => setShowNotification(false)}
            style={styles.closeButton}
          >
            ✕
          </button>
        </div>
      )}

      {/* Header */}
      <div style={styles.header}>
        <h1>📦 Order Management</h1>
        <div style={styles.headerInfo}>
          <div style={styles.badge}>
            <span style={styles.badgeNumber}>{newOrderCount}</span>
            <span>New Orders</span>
          </div>
        </div>
      </div>

      {/* Filter Tabs */}
      <div style={styles.filterTabs}>
        {['pending_confirmation', 'confirmed', 'processing', 'shipped'].map(status => (
          <button
            key={status}
            onClick={() => setFilter(status)}
            style={{
              ...styles.filterButton,
              ...(filter === status ? styles.filterButtonActive : {}),
            }}
          >
            {status.replace('_', ' ').toUpperCase()}
          </button>
        ))}
      </div>

      {/* Loading State */}
      {loading && <p style={styles.loading}>Loading orders...</p>}

      {/* Orders List */}
      <div style={styles.ordersList}>
        {orders.length === 0 ? (
          <div style={styles.emptyState}>
            <p>No orders found</p>
          </div>
        ) : (
          orders.map(order => (
            <div key={order.id} style={styles.orderCard}>
              {/* Order Header */}
              <div style={styles.orderHeader}>
                <div>
                  <h3>Order #{order.orderRef || order.id}</h3>
                  <small>{new Date(order.created_at).toLocaleString()}</small>
                </div>
                <div style={styles.orderStatus}>
                  <span style={{
                    ...styles.statusBadge,
                    backgroundColor: getStatusColor(order.status)
                  }}>
                    {order.status?.replace('_', ' ').toUpperCase()}
                  </span>
                </div>
              </div>

              {/* Customer Info */}
              <div style={styles.customerInfo}>
                <div>
                  <strong>Customer:</strong> {order.customerName}
                </div>
                <div>
                  <strong>Phone:</strong> {order.customerPhone}
                </div>
                <div>
                  <strong>Payment Method:</strong> {order.paymentMethod?.toUpperCase()}
                </div>
              </div>

              {/* Items */}
              <div style={styles.itemsList}>
                <strong>Items ({order.items?.length || 0}):</strong>
                <ul style={styles.items}>
                  {order.items?.map((item, idx) => (
                    <li key={idx}>
                      {item.name || `Product #${item.product_id}`} × {item.quantity} 
                      {' - '}₱{(item.price * item.quantity).toFixed(2)}
                    </li>
                  ))}
                </ul>
              </div>

              {/* Shipping Address */}
              <div style={styles.shippingInfo}>
                <strong>Shipping Address:</strong>
                <p>{order.shippingAddress?.street}</p>
                <p>
                  {order.shippingAddress?.barangay}, {order.shippingAddress?.city}, 
                  {' '}{order.shippingAddress?.province} {order.shippingAddress?.postalCode}
                </p>
              </div>

              {/* Order Total */}
              <div style={styles.orderTotal}>
                <strong>Total: ₱{order.total?.toFixed(2)}</strong>
              </div>

              {/* Action Buttons */}
              <div style={styles.actionButtons}>
                {order.status === 'pending_confirmation' && (
                  <>
                    <button
                      onClick={() => handleConfirmOrder(order.id)}
                      style={styles.confirmButton}
                    >
                      ✓ Confirm Order
                    </button>
                    <button
                      onClick={() => handleMarkAsProcessing(order.id)}
                      style={styles.processingButton}
                    >
                      ⚙ Processing
                    </button>
                  </>
                )}
                {order.status === 'confirmed' && (
                  <button
                    onClick={() => handleMarkAsProcessing(order.id)}
                    style={styles.processingButton}
                  >
                    ⚙ Mark as Processing
                  </button>
                )}
                <button
                  style={styles.viewButton}
                  onClick={() => console.log('View details', order.id)}
                >
                  👁 View Details
                </button>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

const getStatusColor = (status) => {
  const colors = {
    'pending_confirmation': '#FFC107',
    'confirmed': '#2196F3',
    'processing': '#FF9800',
    'shipped': '#9C27B0',
    'delivered': '#4CAF50',
    'cancelled': '#F44336',
  };
  return colors[status] || '#757575';
};

const styles = {
  container: {
    maxWidth: '1200px',
    margin: '0 auto',
    padding: '20px',
    fontFamily: 'Arial, sans-serif',
    backgroundColor: '#f5f5f5',
  },
  notificationBanner: {
    display: 'flex',
    alignItems: 'center',
    gap: '15px',
    padding: '15px 20px',
    marginBottom: '20px',
    backgroundColor: '#4CAF50',
    color: 'white',
    borderRadius: '8px',
    boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
    animation: 'slideDown 0.3s ease-out',
  },
  notificationBell: {
    fontSize: '24px',
  },
  notificationContent: {
    flex: 1,
  },
  closeButton: {
    background: 'none',
    border: 'none',
    color: 'white',
    fontSize: '20px',
    cursor: 'pointer',
    padding: '0',
  },
  header: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: '30px',
    paddingBottom: '20px',
    borderBottom: '2px solid #ddd',
  },
  headerInfo: {
    display: 'flex',
    gap: '10px',
  },
  badge: {
    display: 'flex',
    alignItems: 'center',
    gap: '8px',
    padding: '8px 16px',
    backgroundColor: '#FF5722',
    color: 'white',
    borderRadius: '20px',
    fontWeight: 'bold',
  },
  badgeNumber: {
    fontSize: '18px',
  },
  filterTabs: {
    display: 'flex',
    gap: '10px',
    marginBottom: '20px',
  },
  filterButton: {
    padding: '10px 15px',
    border: '1px solid #ddd',
    backgroundColor: 'white',
    cursor: 'pointer',
    borderRadius: '4px',
    transition: 'all 0.3s',
  },
  filterButtonActive: {
    backgroundColor: '#2196F3',
    color: 'white',
    borderColor: '#2196F3',
  },
  loading: {
    textAlign: 'center',
    color: '#666',
    padding: '40px 20px',
  },
  ordersList: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fill, minmax(500px, 1fr))',
    gap: '20px',
  },
  emptyState: {
    gridColumn: '1 / -1',
    textAlign: 'center',
    padding: '60px 20px',
    color: '#999',
  },
  orderCard: {
    backgroundColor: 'white',
    borderRadius: '8px',
    padding: '20px',
    boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
    borderLeft: '4px solid #2196F3',
  },
  orderHeader: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'start',
    marginBottom: '15px',
    paddingBottom: '10px',
    borderBottom: '1px solid #eee',
  },
  orderStatus: {
    display: 'flex',
    alignItems: 'center',
  },
  statusBadge: {
    padding: '4px 12px',
    color: 'white',
    borderRadius: '4px',
    fontSize: '12px',
    fontWeight: 'bold',
  },
  customerInfo: {
    marginBottom: '15px',
    fontSize: '14px',
    lineHeight: '1.8',
  },
  itemsList: {
    marginBottom: '15px',
    fontSize: '14px',
  },
  items: {
    listStyle: 'none',
    padding: '10px 0 0 0',
    margin: '0',
  },
  shippingInfo: {
    marginBottom: '15px',
    fontSize: '14px',
    lineHeight: '1.6',
    color: '#555',
  },
  orderTotal: {
    fontSize: '16px',
    marginBottom: '15px',
    padding: '10px',
    backgroundColor: '#f9f9f9',
    borderRadius: '4px',
    color: '#2196F3',
  },
  actionButtons: {
    display: 'flex',
    gap: '10px',
    marginTop: '15px',
  },
  confirmButton: {
    flex: 1,
    padding: '10px 15px',
    backgroundColor: '#4CAF50',
    color: 'white',
    border: 'none',
    borderRadius: '4px',
    cursor: 'pointer',
    fontWeight: 'bold',
    transition: 'background 0.3s',
  },
  processingButton: {
    flex: 1,
    padding: '10px 15px',
    backgroundColor: '#FF9800',
    color: 'white',
    border: 'none',
    borderRadius: '4px',
    cursor: 'pointer',
    fontWeight: 'bold',
    transition: 'background 0.3s',
  },
  viewButton: {
    flex: 1,
    padding: '10px 15px',
    backgroundColor: '#2196F3',
    color: 'white',
    border: 'none',
    borderRadius: '4px',
    cursor: 'pointer',
    transition: 'background 0.3s',
  },
};

export default AdminOrderDashboard;
