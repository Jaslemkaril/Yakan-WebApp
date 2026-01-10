import AsyncStorage from '@react-native-async-storage/async-storage';
import API_CONFIG from '../config/config';

class ApiService {
  constructor() {
    this.token = null;
    this.baseUrl = API_CONFIG.API_BASE_URL;
    console.log(`[API Service] Initialized with base URL: ${this.baseUrl}`);
    console.log(`[API Service] Request timeout: ${API_CONFIG.REQUEST_TIMEOUT}ms`);
  }

  // ==================== UTILITY METHODS ====================
  
  /**
   * Test API connectivity
   */
  async testConnection() {
    try {
      console.log('[API] Testing connection to server...');
      console.log('[API] Testing URL:', `${this.baseUrl}/products`);
      
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second test timeout
      
      const response = await fetch(`${this.baseUrl}/products`, {
        method: 'GET',
        signal: controller.signal,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });
      
      clearTimeout(timeoutId);
      console.log(`[API] Connection test ${response.ok ? 'SUCCESS ✓' : 'FAILED ✗'} - Status: ${response.status}`);
      return response.ok;
    } catch (error) {
      console.error('[API] Connection test FAILED ✗');
      console.error('[API] Error details:', error.message);
      console.error('[API] Make sure:');
      console.error('  1. Laravel server is running (php artisan serve --host=0.0.0.0 --port=8000)');
      console.error('  2. You are on the same WiFi network as your computer');
      console.error('  3. Windows Firewall allows port 8000');
      console.error('  4. Your IP address is correct in config.js');
      return false;
    }
  }

  /**
   * Set the auth token (called after login/register)
   */
  setToken(token) {
    this.token = token;
  }

  /**
   * Get the auth token from storage
   */
  async getToken() {
    try {
      // Always check storage first to ensure we have the latest token
      const storedToken = await AsyncStorage.getItem('authToken');
      if (storedToken) {
        this.token = storedToken;
      }
      return this.token;
    } catch (error) {
      console.error('Error getting token:', error);
      return null;
    }
  }

  /**
   * Save token to storage
   */
  async saveToken(token) {
    try {
      await AsyncStorage.setItem('authToken', token);
      this.token = token;
    } catch (error) {
      console.error('Error saving token:', error);
    }
  }

  /**
   * Clear token and logout
   */
  async clearToken() {
    try {
      await AsyncStorage.removeItem('authToken');
      this.token = null;
    } catch (error) {
      console.error('Error clearing token:', error);
    }
  }

  /**
   * Make HTTP request with auth header
   */
  async request(method, endpoint, data = null, isFormData = false) {
    try {
      const token = await this.getToken();
      const url = `${this.baseUrl}${endpoint}`;
      
      const headers = {
        'Accept': 'application/json',
        'ngrok-skip-browser-warning': 'true',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      };

      // Don't set Content-Type for FormData - let React Native handle it with boundary
      if (!isFormData) {
        headers['Content-Type'] = 'application/json';
      }

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
        console.log(`[API] Auth token present: ${token.substring(0, 20)}...`);
      } else {
        console.warn(`[API] WARNING: No auth token for ${method} ${endpoint}`);
      }

      const config = {
        method,
        headers,
      };

      if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        config.body = isFormData ? data : JSON.stringify(data);
      }

      console.log(`[API] ${method} ${endpoint}`);
      console.log(`[API] URL: ${url}`);
      
      // Create AbortController for timeout with longer duration for mobile
      const controller = new AbortController();
      const timeoutId = setTimeout(() => {
        controller.abort();
        console.warn(`[API] ⏱️ Request timeout after ${API_CONFIG.REQUEST_TIMEOUT}ms for ${method} ${endpoint}`);
      }, API_CONFIG.REQUEST_TIMEOUT);
      
      // Add signal to config
      config.signal = controller.signal;
      
      let response;
      try {
        response = await fetch(url, config);
        clearTimeout(timeoutId);
      } catch (error) {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
          console.error(`[API] ⏱️ Request timeout for ${method} ${endpoint} after ${API_CONFIG.REQUEST_TIMEOUT}ms`);
          console.error(`[API] 💡 Try: 1) Check WiFi connection 2) Server running? 3) Restart app`);
          throw new Error(`Connection timeout (${API_CONFIG.REQUEST_TIMEOUT/1000}s). Please check:\n1. WiFi connection\n2. Server is running\n3. Both on same network`);
        }
        console.error(`[API] ❌ Network error for ${method} ${endpoint}:`, error.message);
        throw new Error('Network error - Unable to reach server. Please check your WiFi connection and ensure the server is running.');
      }
      
      console.log(`[API] Response status: ${response.status}`);
      
      // Try to parse as JSON, handle non-JSON responses
      let responseText = await response.text();
      
      // Remove BOM (Byte Order Mark) if present
      if (responseText.charCodeAt(0) === 0xFEFF) {
        responseText = responseText.slice(1);
      }
      
      // Trim whitespace
      responseText = responseText.trim();
      
      console.log(`[API] Response length: ${responseText.length} chars`);
      
      let responseData;
      try {
        responseData = JSON.parse(responseText);
        console.log(`[API] Successfully parsed JSON response`);
      } catch (parseError) {
        console.error(`[API Error] Failed to parse JSON from ${endpoint}. Status: ${response.status}`);
        console.error(`[API Debug] Response text: ${responseText.substring(0, 300)}`);
        throw new Error(`Invalid response format from server (${response.status}). Please check your API endpoint and ensure the backend is running.`);
      }

      if (!response.ok) {
        throw new Error(responseData.message || `HTTP Error: ${response.status}`);
      }

      console.log(`[API] Response data:`, JSON.stringify(responseData).substring(0, 500));

      return {
        success: true,
        data: responseData,
        status: response.status,
      };
    } catch (error) {
      // Don't log 404 errors as errors - they're expected for deleted resources
      if (error.message?.includes('Order not found')) {
        console.log(`[API] Resource not found: ${endpoint}`);
      } else {
        console.error(`[API Error] ${method} ${endpoint}:`, error);
      }
      return {
        success: false,
        error: error.message,
        data: null,
      };
    }
  }

  // ==================== AUTH ENDPOINTS ====================

  /**
   * Register new user
   */
  async register(firstName, lastName, email, password, confirmPassword) {
    const response = await this.request('POST', API_CONFIG.ENDPOINTS.AUTH.REGISTER, {
      first_name: firstName,
      last_name: lastName,
      email,
      password,
      password_confirmation: confirmPassword,
    });

    if (response.success) {
      // The response has nested data structure: response.data.data contains token and user
      const innerData = response.data?.data || response.data;
      const token = innerData?.token;
      
      if (token) {
        await this.saveToken(token);
      }
    }

    return response;
  }

  /**
   * Login user
   */
  async login(email, password) {
    const response = await this.request('POST', API_CONFIG.ENDPOINTS.AUTH.LOGIN, {
      email,
      password,
    });

    if (response.success) {
      // The response has nested data structure: response.data.data contains token and user
      const innerData = response.data?.data || response.data;
      const token = innerData?.token;
      
      if (token) {
        await this.saveToken(token);
      }
    }

    return response;
  }

  /**
   * Logout user
   */
  async logout() {
    const response = await this.request('POST', API_CONFIG.ENDPOINTS.AUTH.LOGOUT);
    await this.clearToken();
    return response;
  }

  /**
   * Get current user info
   */
  async getCurrentUser() {
    return this.request('GET', API_CONFIG.ENDPOINTS.AUTH.GET_USER);
  }

  /**
   * Refresh auth token
   */
  async refreshToken() {
    const response = await this.request('POST', API_CONFIG.ENDPOINTS.AUTH.REFRESH_TOKEN);
    
    if (response.success && response.data.token) {
      await this.saveToken(response.data.token);
    }

    return response;
  }

  // ==================== PRODUCTS ENDPOINTS ====================

  /**
   * Get all products
   */
  async getProducts(filters = {}) {
    const queryString = new URLSearchParams(filters).toString();
    const endpoint = queryString 
      ? `${API_CONFIG.ENDPOINTS.PRODUCTS.LIST}?${queryString}`
      : API_CONFIG.ENDPOINTS.PRODUCTS.LIST;
    
    return this.request('GET', endpoint);
  }

  /**
   * Get single product
   */
  async getProduct(productId) {
    const endpoint = API_CONFIG.ENDPOINTS.PRODUCTS.GET.replace(':id', productId);
    return this.request('GET', endpoint);
  }

  /**
   * Search products
   */
  async searchProducts(query) {
    const endpoint = `${API_CONFIG.ENDPOINTS.PRODUCTS.SEARCH}?q=${encodeURIComponent(query)}`;
    return this.request('GET', endpoint);
  }

  // ==================== ORDERS ENDPOINTS ====================

  /**
   * Create new order
   */
  async createOrder(orderData) {
    // Add mobile source and device info
    const dataWithSource = {
      ...orderData,
      source: 'mobile',
      device_id: 'mobile-app', // You can add unique device ID here if needed
    };
    return this.request('POST', API_CONFIG.ENDPOINTS.ORDERS.CREATE, dataWithSource);
  }

  /**
   * Get user's orders
   */
  async getOrders(filters = {}) {
    const queryString = new URLSearchParams(filters).toString();
    const endpoint = queryString 
      ? `${API_CONFIG.ENDPOINTS.ORDERS.LIST}?${queryString}`
      : API_CONFIG.ENDPOINTS.ORDERS.LIST;
    
    return this.request('GET', endpoint);
  }

  /**
   * Get single order details
   */
  async getOrder(orderId) {
    const endpoint = API_CONFIG.ENDPOINTS.ORDERS.GET.replace(':id', orderId);
    return this.request('GET', endpoint);
  }

  /**
   * Update order
   */
  async updateOrder(orderId, updates) {
    const endpoint = API_CONFIG.ENDPOINTS.ORDERS.UPDATE.replace(':id', orderId);
    return this.request('PUT', endpoint, updates);
  }

  /**
   * Cancel order
   */
  async cancelOrder(orderId, reason = '') {
    const endpoint = API_CONFIG.ENDPOINTS.ORDERS.CANCEL.replace(':id', orderId);
    return this.request('POST', endpoint, { reason });
  }

  /**
   * Get order status
   */
  async getOrderStatus(orderId) {
    const endpoint = API_CONFIG.ENDPOINTS.ORDERS.STATUS.replace(':id', orderId);
    return this.request('GET', endpoint);
  }

  // ==================== SHIPPING ENDPOINTS ====================

  /**
   * Get active shipping rate
   */
  async getShippingRate() {
    return this.request('GET', '/shipping/rate');
  }

  /**
   * Calculate shipping fee based on distance or coordinates
   */
  async calculateShippingFee(data) {
    // Support both old format (just distance) and new format (with coordinates)
    const payload = typeof data === 'number' 
      ? { distance_km: data }
      : data;
    
    console.log('[API] Shipping fee payload:', JSON.stringify(payload));
    
    // Build query string for GET request
    const queryParams = new URLSearchParams(payload).toString();
    return this.request('GET', `/shipping/calculate-fee?${queryParams}`);
  }

  // ==================== PAYMENT ENDPOINTS ====================

  /**
   * Upload payment proof
   */
  async uploadPaymentProof(orderId, imageUri) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    
    const fileName = imageUri.split('/').pop();
    const mimeType = 'image/jpeg';
    
    formData.append('proof_image', {
      uri: imageUri,
      type: mimeType,
      name: fileName,
    });

    return this.request(
      'POST',
      API_CONFIG.ENDPOINTS.PAYMENT.UPLOAD_PROOF,
      formData,
      true // isFormData
    );
  }

  /**
   * Verify payment
   */
  async verifyPayment(orderId) {
    const endpoint = API_CONFIG.ENDPOINTS.PAYMENT.VERIFY;
    return this.request('POST', endpoint, { order_id: orderId });
  }

  /**
   * Get payment status
   */
  async getPaymentStatus(orderId) {
    const endpoint = API_CONFIG.ENDPOINTS.PAYMENT.STATUS.replace(':orderId', orderId);
    return this.request('GET', endpoint);
  }

  // ==================== USER ENDPOINTS ====================

  /**
   * Get user profile
   */
  async getUserProfile() {
    return this.request('GET', API_CONFIG.ENDPOINTS.USER.GET_PROFILE);
  }

  /**
   * Update user profile
   */
  async updateUserProfile(profileData) {
    return this.request('PUT', API_CONFIG.ENDPOINTS.USER.UPDATE_PROFILE, profileData);
  }

  /**
   * Get saved addresses
   */
  async getSavedAddresses() {
    return this.request('GET', API_CONFIG.ENDPOINTS.USER.GET_ADDRESSES);
  }

  /**
   * Create new address
   */
  async createAddress(addressData) {
    return this.request('POST', API_CONFIG.ENDPOINTS.USER.CREATE_ADDRESS, addressData);
  }

  /**
   * Update address
   */
  async updateAddress(addressId, addressData) {
    const endpoint = API_CONFIG.ENDPOINTS.USER.UPDATE_ADDRESS.replace(':id', addressId);
    return this.request('PUT', endpoint, addressData);
  }

  /**
   * Delete address
   */
  async deleteAddress(addressId) {
    const endpoint = API_CONFIG.ENDPOINTS.USER.DELETE_ADDRESS.replace(':id', addressId);
    return this.request('DELETE', endpoint);
  }

  // ==================== CUSTOM ORDERS ENDPOINTS ====================

  /**
   * Get all custom orders
   */
  async getCustomOrders(filters = {}) {
    const queryString = new URLSearchParams(filters).toString();
    const endpoint = queryString 
      ? `/custom-orders?${queryString}`
      : '/custom-orders';
    
    return this.request('GET', endpoint);
  }

  /**
   * Get single custom order
   */
  async getCustomOrder(orderId) {
    return this.request('GET', `/custom-orders/${orderId}`);
  }

  /**
   * Create custom order
   */
  async createCustomOrder(orderData) {
    return this.request('POST', '/custom-orders', orderData);
  }

  /**
   * Update custom order status
   */
  async updateCustomOrderStatus(orderId, status) {
    return this.request('PATCH', `/custom-orders/${orderId}/status`, { status });
  }

  /**
   * Cancel custom order
   */
  async cancelCustomOrder(orderId) {
    return this.request('POST', `/custom-orders/${orderId}/cancel`);
  }

  // ==================== POLLING/REAL-TIME METHODS ====================

  /**
   * Get all chats
   */
  async getChats() {
    console.log('[ChatAPI] Fetching chats with token:', this.token ? 'present' : 'missing');
    const response = await this.request('GET', API_CONFIG.ENDPOINTS.CHAT.LIST);
    console.log('[ChatAPI] getChats response:', response);
    return response;
  }

  /**
   * Get specific chat
   */
  async getChat(chatId) {
    console.log('[ChatAPI] Fetching chat', chatId, 'with token:', this.token ? 'present' : 'missing');
    const endpoint = API_CONFIG.ENDPOINTS.CHAT.GET.replace(':id', chatId);
    const response = await this.request('GET', endpoint);
    console.log('[ChatAPI] getChat response:', response);
    return response;
  }

  /**
   * Create new chat
   */
  async createChat(subject, message) {
    console.log('[ChatAPI] Creating chat with token:', this.token ? 'present' : 'missing');
    return await this.request('POST', API_CONFIG.ENDPOINTS.CHAT.CREATE, {
      subject,
      message,
    });
  }

  /**
   * Send message in chat
   */
  async sendChatMessage(chatId, message, image = null) {
    console.log('[ChatAPI] Sending message with token:', this.token ? 'present' : 'missing');
    console.log('[ChatAPI] Message:', message);
    console.log('[ChatAPI] Image:', image ? 'present' : 'null');
    
    const endpoint = API_CONFIG.ENDPOINTS.CHAT.SEND_MESSAGE.replace(':id', chatId);
    
    if (image) {
      // Use FormData for image upload
      const formData = new FormData();
      
      // Add message if present
      if (message && message.trim()) {
        formData.append('message', message.trim());
        console.log('[ChatAPI] Added message to FormData:', message.trim());
      }
      
      const uriParts = image.uri.split('.');
      const fileType = uriParts[uriParts.length - 1];
      
      const imageData = {
        uri: image.uri,
        name: `chat_image_${Date.now()}.${fileType}`,
        type: `image/${fileType}`,
      };
      
      console.log('[ChatAPI] Image data:', imageData);
      formData.append('image', imageData);
      
      console.log('[ChatAPI] Sending FormData with image');
      return await this.request('POST', endpoint, formData, true);
    }
    
    console.log('[ChatAPI] Sending text-only message');
    return await this.request('POST', endpoint, { message });
  }

  /**
   * Respond to price quote
   */
  async respondToQuote(chatId, response, quoteMessageId) {
    console.log('[ChatAPI] Responding to quote:', response);
    const endpoint = `/chats/${chatId}/respond-quote`;
    return await this.request('POST', endpoint, { 
      response, 
      quote_message_id: quoteMessageId 
    });
  }

  /**
   * Update chat status
   */
  async updateChatStatus(chatId, status) {
    console.log('[ChatAPI] Updating chat status with token:', this.token ? 'present' : 'missing');
    const endpoint = API_CONFIG.ENDPOINTS.CHAT.UPDATE_STATUS.replace(':id', chatId);
    return await this.request('PATCH', endpoint, { status });
  }

  // ==================== WISHLIST ENDPOINTS ====================

  /**
   * Get user's wishlist items
   */
  async getWishlist() {
    console.log('[WishlistAPI] Fetching wishlist...');
    return await this.request('GET', '/wishlist');
  }

  /**
   * Add item to wishlist
   */
  async addToWishlist(productId, type = 'product') {
    console.log('[WishlistAPI] Adding to wishlist:', productId);
    return await this.request('POST', '/wishlist/add', { 
      type, 
      id: productId 
    });
  }

  /**
   * Remove item from wishlist
   */
  async removeFromWishlist(productId, type = 'product') {
    console.log('[WishlistAPI] Removing from wishlist:', productId);
    return await this.request('POST', '/wishlist/remove', { 
      type, 
      id: productId 
    });
  }

  /**
   * Check if item is in wishlist
   */
  async checkWishlist(productId, type = 'product') {
    return await this.request('POST', '/wishlist/check', { 
      type, 
      id: productId 
    });
  }

  // ==================== CART ENDPOINTS ====================

  /**
   * Get user's cart items
   */
  async getCart() {
    console.log('[CartAPI] Fetching cart...');
    return await this.request('GET', '/cart');
  }

  /**
   * Add item to cart
   */
  async addToCart(productId, quantity = 1) {
    console.log('[CartAPI] Adding to cart:', productId, quantity);
    return await this.request('POST', '/cart', { 
      product_id: productId,
      quantity 
    });
  }

  /**
   * Update cart item quantity
   */
  async updateCartItem(cartId, quantity) {
    console.log('[CartAPI] Updating cart item:', cartId, quantity);
    return await this.request('PUT', `/cart/${cartId}`, { quantity });
  }

  /**
   * Remove item from cart
   */
  async removeFromCart(cartId) {
    console.log('[CartAPI] Removing from cart:', cartId);
    return await this.request('DELETE', `/cart/${cartId}`);
  }

  /**
   * Clear entire cart
   */
  async clearCart() {
    console.log('[CartAPI] Clearing cart');
    return await this.request('DELETE', '/cart');
  }

  // ==================== POLLING/REAL-TIME METHODS ====================

  /**
   * Start polling for order status updates
   */
  startOrderPolling(orderId, callback, interval = API_CONFIG.POLLING_INTERVAL) {
    const pollInterval = setInterval(async () => {
      const response = await this.getOrder(orderId);
      if (response.success) {
        callback(response.data);
      }
    }, interval);

    return () => clearInterval(pollInterval); // Return function to stop polling
  }

  /**
   * Start polling for payment status updates
   */
  startPaymentPolling(orderId, callback, interval = API_CONFIG.POLLING_INTERVAL) {
    const pollInterval = setInterval(async () => {
      const response = await this.getPaymentStatus(orderId);
      if (response.success) {
        callback(response.data);
      }
    }, interval);

    return () => clearInterval(pollInterval); // Return function to stop polling
  }
}

// Export singleton instance
export default new ApiService();
