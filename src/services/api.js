import AsyncStorage from '@react-native-async-storage/async-storage';
import API_CONFIG from '../config/config';

class ApiService {
  constructor() {
    this.token = null;
    this.baseUrl = API_CONFIG.API_BASE_URL;
  }

  // ==================== UTILITY METHODS ====================

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
      if (!this.token) {
        this.token = await AsyncStorage.getItem('authToken');
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
        'Content-Type': isFormData ? 'multipart/form-data' : 'application/json',
        'Accept': 'application/json',
        'ngrok-skip-browser-warning': 'true', // Skip ngrok browser warning
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const config = {
        method,
        headers,
        timeout: API_CONFIG.REQUEST_TIMEOUT,
      };

      if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        config.body = isFormData ? data : JSON.stringify(data);
      }

      console.log(`[API] ${method} ${endpoint}`);
      
      const response = await fetch(url, config);
      
      // Try to parse as JSON, handle non-JSON responses
      let responseText = await response.text();
      
      // Remove BOM (Byte Order Mark) if present
      if (responseText.charCodeAt(0) === 0xFEFF) {
        responseText = responseText.slice(1);
      }
      
      let responseData;
      try {
        responseData = JSON.parse(responseText);
      } catch (parseError) {
        console.error(`[API Error] Failed to parse JSON from ${endpoint}. Status: ${response.status}`);
        console.error(`[API Debug] Response text: ${responseText.substring(0, 200)}`);
        throw new Error(`Invalid response format from server (${response.status}). Please check your API endpoint and ensure the backend is running.`);
      }

      if (!response.ok) {
        throw new Error(responseData.message || `HTTP Error: ${response.status}`);
      }

      return {
        success: true,
        data: responseData,
        status: response.status,
      };
    } catch (error) {
      console.error(`[API Error] ${method} ${endpoint}:`, error);
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
    return this.request('POST', API_CONFIG.ENDPOINTS.AUTH.REGISTER, {
      first_name: firstName,
      last_name: lastName,
      email,
      password,
      password_confirmation: confirmPassword,
    });
  }

  /**
   * Login user
   */
  async login(email, password) {
    const response = await this.request('POST', API_CONFIG.ENDPOINTS.AUTH.LOGIN, {
      email,
      password,
    });

    if (response.success && response.data.token) {
      await this.saveToken(response.data.token);
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
