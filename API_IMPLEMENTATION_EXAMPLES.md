/**
 * QUICK REFERENCE - Updating Existing Screens
 * ============================================
 * 
 * Examples of how to use the new API services in your screens
 */

// ============================================================
// LOGIN SCREEN - Connect to Backend
// ============================================================
/*
File: src/screens/LoginScreen.js

OLD CODE:
  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }
    setIsLoading(true);
    setTimeout(() => {
      const userData = {
        name: email.split('@')[0],
        email: email,
      };
      login(userData);
      setIsLoading(false);
      navigation.navigate('Home');
    }, 2000);
  };

NEW CODE:
  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }
    setIsLoading(true);
    const response = await loginWithBackend(email, password);
    setIsLoading(false);
    
    if (response.success) {
      Alert.alert('Success', 'Login successful!');
      navigation.navigate('Home');
    } else {
      Alert.alert('Error', response.message);
    }
  };

REPLACE IN LOGIN SCREEN:
  - Use: const { loginWithBackend } = useCart();
  - Call: await loginWithBackend(email, password)
  - Remove mock setTimeout
*/

// ============================================================
// REGISTER SCREEN - Connect to Backend
// ============================================================
/*
File: src/screens/RegisterScreen.js

OLD CODE:
  const handleRegister = async () => {
    // validation...
    setIsLoading(true);
    setTimeout(() => {
      const fullName = `${firstName} ${middleName} ${lastName}`.trim();
      setIsLoading(false);
      Alert.alert('Success', `Welcome, ${fullName}! Registration successful!`, [
        { text: 'OK', onPress: () => navigation.navigate('Login') }
      ]);
    }, 2000);
  };

NEW CODE:
  const handleRegister = async () => {
    // validation...
    setIsLoading(true);
    const response = await registerWithBackend(
      firstName, 
      lastName, 
      email, 
      password, 
      confirmPassword
    );
    setIsLoading(false);
    
    if (response.success) {
      Alert.alert('Success', 'Registration successful! You are now logged in.');
      navigation.navigate('Home');
    } else {
      Alert.alert('Error', response.message);
    }
  };

REPLACE IN REGISTER SCREEN:
  - Use: const { registerWithBackend } = useCart();
  - Call: await registerWithBackend(...)
  - Remove mock setTimeout
  - Auto-login user after registration
*/

// ============================================================
// PRODUCTS SCREEN - Fetch from Backend
// ============================================================
/*
File: src/screens/ProductsScreen.js

NEW ADDITION:
  import ApiService from '../services/api';
  
  useEffect(() => {
    fetchProducts();
  }, []);

  const fetchProducts = async () => {
    const response = await ApiService.getProducts({
      category: selectedCategory !== 'All' ? selectedCategory : null,
    });
    if (response.success) {
      setProducts(response.data.products || []);
    }
  };

BENEFITS:
  - Real products from Laravel database
  - Dynamic filtering by category
  - Always up-to-date with backend
*/

// ============================================================
// CHECKOUT SCREEN - Create Order
// ============================================================
/*
File: src/screens/CheckoutScreen.js

NEW ADDITION:
  import OrderService from '../services/orderService';
  
  const handlePlaceOrder = async () => {
    // validation...
    setIsLoading(true);
    
    const response = await OrderService.createOrder(
      cartItems,
      addressForm,
      selectedPaymentMethod
    );
    
    setIsLoading(false);
    
    if (response.success) {
      clearCart();
      Alert.alert('Success', 'Order placed successfully!');
      navigation.navigate('OrderDetails', { 
        orderData: response.data.order 
      });
    } else {
      Alert.alert('Error', response.error);
    }
  };

BENEFITS:
  - Orders saved in Laravel database
  - Admin can see orders immediately
  - Order ID returned for tracking
*/

// ============================================================
// TRACK ORDER SCREEN - Real-time Updates
// ============================================================
/*
File: src/screens/TrackOrderScreen.js

NEW ADDITION:
  import OrderService from '../services/orderService';

  useEffect(() => {
    if (order?.id) {
      // Start polling for status updates
      const stopPolling = OrderService.trackOrderStatus(
        order.id,
        (updatedOrder) => {
          setOrder(OrderService.formatOrderForDisplay(updatedOrder));
          
          // Notify user of status changes
          if (updatedOrder.status === 'shipped') {
            Alert.alert('Info', 'Your order has been shipped!');
          }
          if (updatedOrder.status === 'delivered') {
            Alert.alert('Success', 'Your order has been delivered!');
          }
          if (updatedOrder.status === 'payment_verified') {
            Alert.alert('Success', 'Admin verified your payment!');
          }
        }
      );
      
      return () => stopPolling(); // Stop polling on unmount
    }
  }, [order?.id]);

BENEFITS:
  - Order status updates every 10 seconds
  - User sees changes in real-time
  - Admin changes trigger notifications
  - No need to refresh manually
*/

// ============================================================
// PAYMENT SCREEN - Upload Proof
// ============================================================
/*
File: src/screens/PaymentScreen.js

NEW ADDITION:
  import ApiService from '../services/api';
  
  const handleUploadPaymentProof = async (imageUri) => {
    setIsUploading(true);
    
    const response = await ApiService.uploadPaymentProof(
      orderData.id,
      imageUri
    );
    
    setIsUploading(false);
    
    if (response.success) {
      Alert.alert('Success', 'Payment proof uploaded! Admin will verify it soon.');
      // Start polling for payment verification
      const stopPolling = OrderService.trackPaymentStatus(
        orderData.id,
        (paymentData) => {
          if (paymentData.status === 'verified') {
            Alert.alert('Success', 'Payment verified by admin!');
            stopPolling();
          }
        }
      );
    } else {
      Alert.alert('Error', response.error);
    }
  };

BENEFITS:
  - Payment proof stored in Laravel
  - Admin receives proof immediately
  - Real-time verification updates
*/

// ============================================================
// HOME SCREEN - Fetch Featured Products
// ============================================================
/*
File: src/screens/HomeScreen.js

NEW ADDITION:
  import ApiService from '../services/api';
  
  useEffect(() => {
    fetchFeaturedProducts();
  }, []);

  const fetchFeaturedProducts = async () => {
    const response = await ApiService.getProducts({ featured: true });
    if (response.success) {
      setFeaturedProducts(response.data.products || []);
    }
  };

BENEFITS:
  - Featured products from backend
  - Can control featured status in Laravel admin
  - Always current with backend
*/

// ============================================================
// COMMON PATTERNS
// ============================================================
/*
PATTERN 1: Fetch Data on Mount
  useEffect(() => {
    fetchData();
  }, []);

PATTERN 2: Fetch with Filters
  useEffect(() => {
    fetchData({ category, search, sort });
  }, [category, search, sort]);

PATTERN 3: Poll for Updates
  useEffect(() => {
    const stopPolling = ApiService.startOrderPolling(id, callback);
    return () => stopPolling();
  }, [id]);

PATTERN 4: Handle Loading States
  const [loading, setLoading] = useState(false);
  setLoading(true);
  const response = await ApiService.call(...);
  setLoading(false);
  if (response.success) { ... }

PATTERN 5: Show Error Messages
  if (!response.success) {
    Alert.alert('Error', response.error);
  }
*/

export const QUICK_REFERENCE_COMPLETE = true;
