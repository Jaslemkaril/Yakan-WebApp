/**
 * INTEGRATION GUIDE - Mobile App to Laravel Backend
 * ================================================
 * 
 * Follow these steps to connect your React Native app to the Laravel backend
 */

// ============================================================
// STEP 1: Configure API URL
// ============================================================
/*
File: src/config/config.js

Update the API_BASE_URL to point to your Laravel backend:

For Local Development:
  API_BASE_URL: 'http://192.168.1.100:8000/api' // Replace with your machine IP
  
For Production:
  API_BASE_URL: 'https://yourdomain.com/api'

Note: Use IP address instead of localhost for Android emulator/device
*/

// ============================================================
// STEP 2: Required Laravel API Endpoints
// ============================================================
/*
Your Laravel backend needs these endpoints:

AUTH ENDPOINTS:
  POST   /api/auth/register       - Register new user
  POST   /api/auth/login          - Login user
  POST   /api/auth/logout         - Logout user
  GET    /api/auth/user           - Get current user
  POST   /api/auth/refresh-token  - Refresh JWT token

PRODUCT ENDPOINTS:
  GET    /api/products            - Get all products
  GET    /api/products/:id        - Get single product
  GET    /api/products/search     - Search products

ORDER ENDPOINTS:
  POST   /api/orders              - Create new order
  GET    /api/orders              - Get user's orders
  GET    /api/orders/:id          - Get order details
  PUT    /api/orders/:id          - Update order
  POST   /api/orders/:id/cancel   - Cancel order
  GET    /api/orders/:id/status   - Get order status

PAYMENT ENDPOINTS:
  POST   /api/payments/upload-proof     - Upload payment proof
  POST   /api/payments/verify           - Verify payment
  GET    /api/payments/:orderId/status  - Get payment status

USER ENDPOINTS:
  GET    /api/user/profile       - Get user profile
  PUT    /api/user/profile       - Update user profile
  GET    /api/user/addresses     - Get saved addresses
  POST   /api/user/addresses     - Create new address
  PUT    /api/user/addresses/:id - Update address
  DELETE /api/user/addresses/:id - Delete address
*/

// ============================================================
// STEP 3: Authentication Flow (JWT)
// ============================================================
/*
1. User registers/logs in via mobile app
2. Laravel returns JWT token in response
3. Token is saved to device storage (AsyncStorage)
4. All subsequent API requests include token in header:
   Authorization: Bearer <token>
5. Token persists across app restarts
6. On logout, token is cleared

Response format should be:
{
  success: true,
  token: "eyJhbGciOiJIUzI1NiIs...",
  user: {
    id: 1,
    name: "John Doe",
    email: "john@example.com",
    ...
  }
}
*/

// ============================================================
// STEP 4: Using the API Service
// ============================================================
/*
Import and use in your screens:

import ApiService from '../services/api';

// Register
const response = await ApiService.register(
  'John', 'Doe', 'john@example.com', 'password', 'password'
);

// Login
const response = await ApiService.login('john@example.com', 'password');
if (response.success) {
  // Token is auto-saved
  // User is logged in
}

// Get products
const response = await ApiService.getProducts();

// Create order
const response = await ApiService.createOrder(orderData);

// Get user's orders
const response = await ApiService.getOrders();

// Track order status in real-time
const stopPolling = ApiService.startOrderPolling(orderId, (updatedOrder) => {
  console.log('Order updated:', updatedOrder);
});

// Stop polling when needed
stopPolling();
*/

// ============================================================
// STEP 5: Error Handling
// ============================================================
/*
All API responses have this structure:

{
  success: true/false,
  data: { ... },    // Response data
  error: "error message", // Only if success is false
  status: 200       // HTTP status
}

Always check response.success before using data:

const response = await ApiService.login(email, password);
if (response.success) {
  // Login successful
  console.log(response.data.user);
} else {
  // Login failed
  console.error(response.error);
}
*/

// ============================================================
// STEP 6: Real-time Order Updates
// ============================================================
/*
The system uses polling to check for order status updates every 10 seconds.

You can customize:
- Polling interval in src/config/config.js (POLLING_INTERVAL)
- Check for specific status changes in your screen

Example:
const stopPolling = OrderService.trackOrderStatus(orderId, (updatedOrder) => {
  if (updatedOrder.status === 'payment_verified') {
    Alert.alert('Success', 'Admin verified your payment!');
  }
  if (updatedOrder.status === 'shipped') {
    Alert.alert('Info', 'Your order has been shipped!');
  }
});

// Stop polling when screen unmounts
useEffect(() => {
  return () => {
    stopPolling();
  };
}, []);
*/

// ============================================================
// STEP 7: Integration Checklist
// ============================================================
/*
Before deploying:

☐ Update API_BASE_URL in src/config/config.js
☐ Create Laravel API endpoints (see STEP 2)
☐ Test register endpoint
☐ Test login endpoint
☐ Test product endpoints
☐ Test order creation
☐ Test order status updates
☐ Test payment proof upload
☐ Set CORS headers in Laravel (allow mobile app domain)
☐ Test on actual device/emulator

CORS Configuration (Laravel):
Add to .env or config:
CORS_ALLOWED_ORIGINS=*
Or be specific:
CORS_ALLOWED_ORIGINS=http://192.168.1.100:*
*/

// ============================================================
// STEP 8: Troubleshooting
// ============================================================
/*
Issue: "Network Error" when making requests
Solution: 
  - Check API_BASE_URL is correct
  - Check Laravel backend is running
  - For Android emulator, use 10.0.2.2 instead of localhost
  - For device, use your machine's IP address
  - Check CORS headers in Laravel

Issue: Token not persisting
Solution:
  - Check AsyncStorage package is installed
  - Verify token is being saved after login
  - Check token format from Laravel

Issue: Polling not updating
Solution:
  - Check polling interval in config
  - Verify order status is changing in Laravel
  - Check API endpoint returns correct data
  - Check authentication token is valid

Issue: Login always fails
Solution:
  - Verify credentials are correct
  - Check Laravel login endpoint is working
  - Check user exists in database
  - Check password hashing is correct
*/

export const INTEGRATION_COMPLETE = true;
