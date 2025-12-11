import ApiService from './api';

/**
 * Order Service - handles all order-related operations
 */
class OrderService {
  /**
   * Create a new order
   */
  async createOrder(cartItems, shippingAddress, paymentMethod = null) {
    const orderData = {
      items: cartItems.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.price,
      })),
      shipping_address: {
        full_name: shippingAddress.fullName,
        phone_number: shippingAddress.phoneNumber,
        region: shippingAddress.region,
        province: shippingAddress.province,
        city: shippingAddress.city,
        barangay: shippingAddress.barangay,
        postal_code: shippingAddress.postalCode,
        street: shippingAddress.street,
      },
      payment_method: paymentMethod,
      special_notes: shippingAddress.notes || '',
    };

    return await ApiService.createOrder(orderData);
  }

  /**
   * Get user's orders with optional filters
   */
  async getUserOrders(filters = {}) {
    const defaultFilters = {
      sort: '-created_at', // Sort by newest first
      ...filters,
    };
    return await ApiService.getOrders(defaultFilters);
  }

  /**
   * Get single order with real-time status
   */
  async getOrderWithStatus(orderId) {
    return await ApiService.getOrder(orderId);
  }

  /**
   * Track order status changes in real-time
   */
  trackOrderStatus(orderId, onStatusChange) {
    // Use polling to check for status updates
    return ApiService.startOrderPolling(orderId, (orderData) => {
      if (orderData.status) {
        onStatusChange(orderData);
      }
    });
  }

  /**
   * Cancel an order
   */
  async cancelOrder(orderId, reason = '') {
    return await ApiService.cancelOrder(orderId, reason);
  }

  /**
   * Upload payment proof for an order
   */
  async uploadPaymentProof(orderId, imageUri) {
    return await ApiService.uploadPaymentProof(orderId, imageUri);
  }

  /**
   * Get order status
   */
  async getOrderStatus(orderId) {
    return await ApiService.getOrderStatus(orderId);
  }

  /**
   * Track payment status in real-time
   */
  trackPaymentStatus(orderId, onStatusChange) {
    return ApiService.startPaymentPolling(orderId, (paymentData) => {
      if (paymentData.status) {
        onStatusChange(paymentData);
      }
    });
  }

  /**
   * Format order for display
   */
  formatOrderForDisplay(order) {
    return {
      id: order.id,
      orderRef: order.order_ref || order.id,
      date: order.created_at,
      status: order.status,
      statusLabel: this.getStatusLabel(order.status),
      statusColor: this.getStatusColor(order.status),
      items: order.items || [],
      subtotal: order.subtotal || 0,
      shippingFee: order.shipping_fee || 0,
      total: order.total || 0,
      paymentMethod: order.payment_method,
      paymentStatus: order.payment_status,
      shippingAddress: order.shipping_address,
      timeline: order.timeline || [],
    };
  }

  /**
   * Get human-readable status label
   */
  getStatusLabel(status) {
    const labels = {
      pending_confirmation: 'Pending Confirmation',
      confirmed: 'Confirmed',
      processing: 'Processing',
      shipped: 'Shipped',
      delivered: 'Delivered',
      cancelled: 'Cancelled',
      payment_pending: 'Payment Pending',
      payment_verified: 'Payment Verified',
    };
    return labels[status] || status;
  }

  /**
   * Get status color for UI
   */
  getStatusColor(status) {
    const colors = {
      pending_confirmation: '#FFC107',
      confirmed: '#2196F3',
      processing: '#FF9800',
      shipped: '#9C27B0',
      delivered: '#4CAF50',
      cancelled: '#F44336',
      payment_pending: '#FF9800',
      payment_verified: '#4CAF50',
    };
    return colors[status] || '#757575';
  }
}

export default new OrderService();
