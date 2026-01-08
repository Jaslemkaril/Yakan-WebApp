import colors from './colors';

export const trackingStages = [
  {
    key: 'pending',
    label: 'Pending',
    description: 'Order received and awaiting confirmation.',
    iconName: 'clipboard-outline',
    iconLibrary: 'Ionicons',
    color: '#FF9800',
  },
  {
    key: 'processing',
    label: 'Processing',
    description: 'Your items are being prepared for shipment.',
    iconName: 'cog-outline',
    iconLibrary: 'Ionicons',
    color: colors.primary,
  },
  {
    key: 'shipped',
    label: 'Shipping',
    description: 'Your order is on its way to you.',
    iconName: 'car-outline',
    iconLibrary: 'Ionicons',
    color: '#2196F3',
  },
  {
    key: 'delivered',
    label: 'Delivered',
    description: 'Your order has been delivered.',
    iconName: 'checkmark-circle-outline',
    iconLibrary: 'Ionicons',
    color: '#4CAF50',
  },
];