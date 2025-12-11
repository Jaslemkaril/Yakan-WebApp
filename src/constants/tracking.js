import colors from './colors';

export const trackingStages = [
  {
    key: 'pending_payment',
    label: 'Pending Payment',
    description: 'Waiting for you to complete the payment.',
    icon: '💰',
    color: '#FF9800',
  },
  {
    key: 'payment_verified',
    label: 'Payment Verified',
    description: 'Your payment has been successfully verified.',
    icon: '💳',
    color: '#2196F3',
  },
  {
    key: 'pending_confirmation',
    label: 'Order Confirmed',
    description: 'Your order has been confirmed and will be processed soon.',
    icon: '✓',
    color: colors.primary,
  },
  {
    key: 'processing',
    label: 'Processing',
    description: 'Your items are being prepared for shipment.',
    icon: '📦',
    color: '#9C27B0',
  },
  {
    key: 'shipped',
    label: 'Shipped',
    description: 'Your order is on its way to you.',
    icon: '🚚',
    color: '#00BCD4',
  },
  {
    key: 'delivered',
    label: 'Delivered',
    description: 'Your order has been delivered.',
    icon: '✅',
    color: '#4CAF50',
  },
  {
    key: 'cancelled',
    label: 'Cancelled',
    description: 'This order has been cancelled.',
    icon: '❌',
    color: '#F44336',
  },
];