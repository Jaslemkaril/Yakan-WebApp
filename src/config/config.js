// Configuration file for API and app settings
// Update the API_BASE_URL when your Laravel backend is ready

import { Platform } from 'react-native';

// Detect if running on emulator or physical device
const getApiBaseUrl = () => {
  // ✅ DEPLOYED ON RAILWAY - Production URL
  return 'https://yakan-webapp-production.up.railway.app/api/v1';
  
  // 🔧 For local development, uncomment one of these:
  // const MACHINE_IP = '192.168.47.5';
  // const PORT = '8000';
  // return `http://10.0.2.2:${PORT}/api/v1`; // Android Emulator
  // return `http://localhost:${PORT}/api/v1`; // iOS Simulator
  // return `http://${MACHINE_IP}:${PORT}/api/v1`; // Physical Device
};

const getStorageBaseUrl = () => {
  // ✅ DEPLOYED ON RAILWAY - Production URL
  return 'https://yakan-webapp-production.up.railway.app/storage';
  
  // 🔧 For local development, uncomment one of these:
  // const MACHINE_IP = '192.168.47.5';
  // const PORT = '8000';
  // return `http://10.0.2.2:${PORT}/storage`; // Android Emulator
  // return `http://localhost:${PORT}/storage`; // iOS Simulator
  // return `http://${MACHINE_IP}:${PORT}/storage`; // Physical Device
};

export const API_CONFIG = {
  // ⚠️ API Base URLs
  API_BASE_URL: getApiBaseUrl(),
  
  // Base URL for storage/uploads (images, files, etc.)
  STORAGE_BASE_URL: getStorageBaseUrl(),
  
  // Polling interval for order status updates (in milliseconds)
  POLLING_INTERVAL: 10000, // 10 seconds
  
  // Request timeout - increased for network latency and slow server response
  REQUEST_TIMEOUT: 120000, // 120 seconds (2 minutes)
  
  // API Endpoints
  ENDPOINTS: {
    // Auth endpoints
    AUTH: {
      REGISTER: '/register',
      LOGIN: '/login',
      LOGOUT: '/logout',
      REFRESH_TOKEN: '/refresh-token',
      GET_USER: '/user',
    },
    
    // Products endpoints
    PRODUCTS: {
      LIST: '/products',
      GET: '/products/:id',
      SEARCH: '/products/search',
    },
    
    // Cart/Order endpoints
    ORDERS: {
      CREATE: '/orders',
      LIST: '/orders',
      GET: '/orders/:id',
      UPDATE: '/orders/:id',
      CANCEL: '/orders/:id/cancel',
      STATUS: '/orders/:id/status',
    },
    
    // Payment endpoints
    PAYMENT: {
      UPLOAD_PROOF: '/payments/upload-proof',
      VERIFY: '/payments/verify',
      STATUS: '/payments/:orderId/status',
    },
    
    // User endpoints
    USER: {
      GET_PROFILE: '/user/profile',
      UPDATE_PROFILE: '/user/profile',
      GET_ADDRESSES: '/addresses',
      CREATE_ADDRESS: '/addresses',
      UPDATE_ADDRESS: '/addresses/:id',
      DELETE_ADDRESS: '/addresses/:id',
    },

    // Chat endpoints
    CHAT: {
      LIST: '/chats',
      GET: '/chats/:id',
      CREATE: '/chats',
      SEND_MESSAGE: '/chats/:id/messages',
      UPDATE_STATUS: '/chats/:id/status',
    },

    // Custom Orders endpoints
    CUSTOM_ORDERS: {
      LIST: '/custom-orders',
      GET: '/custom-orders/:id',
      CREATE: '/custom-orders',
      UPDATE_STATUS: '/custom-orders/:id/status',
      CANCEL: '/custom-orders/:id/cancel',
    },

    // Shipping endpoints
    SHIPPING: {
      GET_RATE: '/shipping/rate',
      CALCULATE_FEE: '/shipping/calculate-fee',
    },
  },
};

export default API_CONFIG;
