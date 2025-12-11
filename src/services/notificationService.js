import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from './api';

/**
 * Real-time notification service for order updates
 * Polls the backend for order status changes
 */
class NotificationService {
  constructor() {
    this.pollingIntervals = {}; // Track polling intervals per order
    this.listeners = {}; // Store listeners for each event type
  }

  /**
   * Subscribe to notifications
   */
  on(eventType, callback) {
    if (!this.listeners[eventType]) {
      this.listeners[eventType] = [];
    }
    this.listeners[eventType].push(callback);

    // Return unsubscribe function
    return () => {
      this.listeners[eventType] = this.listeners[eventType].filter(cb => cb !== callback);
    };
  }

  /**
   * Emit notification event
   */
  emit(eventType, data) {
    if (this.listeners[eventType]) {
      this.listeners[eventType].forEach(callback => {
        try {
          callback(data);
        } catch (error) {
          console.error(`Error in ${eventType} listener:`, error);
        }
      });
    }
  }

  /**
   * Start polling for order status updates
   * @param {string} orderId - The order ID to poll
   * @param {function} onUpdate - Callback when status changes
   * @param {number} interval - Polling interval in ms (default: 15 seconds)
   */
  startOrderStatusPolling(orderId, onUpdate, interval = 15000) {
    // Clear existing poll if any
    this.stopOrderStatusPolling(orderId);

    const pollFunction = async () => {
      try {
        const order = await ApiService.getOrder(orderId);
        
        if (order && order.data) {
          // Emit notification
          this.emit('orderStatusChanged', {
            orderId,
            order: order.data,
            timestamp: new Date(),
          });

          // Call the callback
          if (onUpdate) {
            onUpdate(order.data);
          }

          // Cache the order locally
          await this.cacheOrderUpdate(orderId, order.data);
        }
      } catch (error) {
        console.warn(`Error polling order ${orderId}:`, error.message);
      }
    };

    // Start polling
    const intervalId = setInterval(pollFunction, interval);
    this.pollingIntervals[orderId] = intervalId;

    // Poll immediately first
    pollFunction();

    return () => this.stopOrderStatusPolling(orderId);
  }

  /**
   * Stop polling for a specific order
   */
  stopOrderStatusPolling(orderId) {
    if (this.pollingIntervals[orderId]) {
      clearInterval(this.pollingIntervals[orderId]);
      delete this.pollingIntervals[orderId];
    }
  }

  /**
   * Stop all polling
   */
  stopAllPolling() {
    Object.keys(this.pollingIntervals).forEach(orderId => {
      this.stopOrderStatusPolling(orderId);
    });
  }

  /**
   * Cache order update locally
   */
  async cacheOrderUpdate(orderId, orderData) {
    try {
      const key = `order_cache_${orderId}`;
      await AsyncStorage.setItem(key, JSON.stringify({
        ...orderData,
        cachedAt: new Date().toISOString(),
      }));
    } catch (error) {
      console.warn('Error caching order update:', error);
    }
  }

  /**
   * Get cached order data
   */
  async getCachedOrder(orderId) {
    try {
      const key = `order_cache_${orderId}`;
      const cached = await AsyncStorage.getItem(key);
      return cached ? JSON.parse(cached) : null;
    } catch (error) {
      console.warn('Error getting cached order:', error);
      return null;
    }
  }

  /**
   * Listen for new order notifications (for web admin)
   */
  onNewOrder(callback) {
    return this.on('newOrderCreated', callback);
  }

  /**
   * Listen for order status changes (for mobile user)
   */
  onOrderStatusChange(callback) {
    return this.on('orderStatusChanged', callback);
  }

  /**
   * Listen for admin notifications (for web admin)
   */
  onAdminNotification(callback) {
    return this.on('adminNotification', callback);
  }

  /**
   * Notify about new order creation
   * This is called from mobile when submitting order
   */
  notifyNewOrder(orderData) {
    this.emit('newOrderCreated', {
      order: orderData,
      timestamp: new Date(),
      source: 'mobile',
    });
  }

  /**
   * Get notification history
   */
  async getNotificationHistory(limit = 50) {
    try {
      const key = 'notification_history';
      const history = await AsyncStorage.getItem(key);
      const notifications = history ? JSON.parse(history) : [];
      return notifications.slice(-limit);
    } catch (error) {
      console.warn('Error getting notification history:', error);
      return [];
    }
  }

  /**
   * Add notification to history
   */
  async addNotificationToHistory(notification) {
    try {
      const key = 'notification_history';
      const history = await AsyncStorage.getItem(key);
      const notifications = history ? JSON.parse(history) : [];
      
      notifications.push({
        ...notification,
        id: Date.now(),
        timestamp: new Date().toISOString(),
      });

      // Keep only last 100 notifications
      const recentNotifications = notifications.slice(-100);
      await AsyncStorage.setItem(key, JSON.stringify(recentNotifications));
    } catch (error) {
      console.warn('Error adding notification to history:', error);
    }
  }
}

export default new NotificationService();
