# 🎨 YAKAN Frontend - Component Architecture & React Native Guide

**Document**: Frontend Component & Architecture Reference  
**Technology**: React Native (Expo)  
**Version**: 1.0  
**Last Updated**: February 8, 2026

---

## 📱 Screen Organization (25 Screens)

### Screen Hierarchy & Navigation Graph

```
┌─────────────────────────────────────────────────────────────┐
│                   ROOT NAVIGATOR                             │
│              (Stack.Navigator)                               │
└─────────────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────┬─────────────────┬──────────────┐
        ↓               ↓                 ↓              ↓
   HomeScreen     LoginScreen       RegisterScreen   ChatScreen
        ↓
    ┌───────────────────────────────────────────────┐
    ├──→ ProductsScreen ───────→ ProductDetailScreen
    │         ↓
    │    ProductsScreen
    │         ↓
    │    CartScreen
    │         ↓
    │    CheckoutScreen
    │         ↓
    │    PaymentScreen
    │         ↓
    │    OrdersScreen ──→ OrderDetailsScreen
    │         ↓
    │    TrackOrderScreen
    │
    ├──→ CustomOrderScreen
    │         ↓
    │    ChatScreen
    │
    ├──→ CulturalHeritageScreen
    │
    ├──→ WishlistScreen
    │
    ├──→ AccountScreen
    │
    ├──→ NotificationsScreen
    │
    ├──→ SavedAddressesScreen
    │
    ├──→ PaymentMethodsScreen
    │
    ├──→ ReviewsScreen
    │
    ├──→ SettingsScreen
    │
    └──→ ForgotPasswordScreen
```

---

## 🎯 Screen Descriptions

### Authentication Screens

#### LoginScreen.js
**Purpose**: User email/password authentication  
**Navigation Flow**: 
- Success → HomeScreen (logged in)
- Forgot Password → ForgotPasswordScreen  
- No Account → RegisterScreen  

**Features**:
- Email & password input validation
- Remember me / Keep signed in option
- Google OAuth button
- Facebook OAuth button
- Error messaging for invalid credentials
- Loading state during authentication

**State Management**:
```javascript
const [email, setEmail] = useState('');
const [password, setPassword] = useState('');
const [loading, setLoading] = useState(false);
const [error, setError] = useState(null);
```

**API Call**:
```javascript
POST /api/v1/login
{
  email: "user@example.com",
  password: "password123"
}
```

---

#### RegisterScreen.js
**Purpose**: New user account creation  
**Navigation Flow**:
- Success → HomeScreen (auto-logged in)
- Already have account → LoginScreen

**Features**:
- Name, email, password input fields
- Password strength validation
- Terms & conditions checkbox
- Google/Facebook signup integration
- Form validation with error messages
- Loading state during registration

**Validation Rules**:
- Email: valid format & unique
- Password: minimum 8 characters, includes uppercase/lowercase/number

---

#### ForgotPasswordScreen.js
**Purpose**: Password recovery via email  
**Features**:
- Email input field
- Send reset link button
- Confirmation message
- Back to login option

---

### Shopping Screens

#### HomeScreen.js
**Purpose**: Landing page with featured products & search  
**Navigation Flow**: 
- Tap product → ProductDetailScreen
- Search → ProductsScreen (filtered)
- View all → ProductsScreen
- Tap cart icon → CartScreen
- Tap wishlist → WishlistScreen
- Tap account → AccountScreen
- Tap notifications → NotificationsScreen
- Tap chat → ChatScreen

**Key Features**:
```javascript
// Featured products carousel
FeaturedProductScroll → Horizontal ScrollView

// Search bar
SearchBar → filters ProductsScreen

// Bottom navigation
BottomNav.js → 5 main tabs
├── Home
├── Products
├── Cart
├── Account
└── Wishlist
```

**State Management**:
```javascript
const [searchQuery, setSearchQuery] = useState('');
const [products, setProducts] = useState([]);
const [featuredProducts, setFeaturedProducts] = useState([]);
const [loading, setLoading] = useState(true);
const [menuOpen, setMenuOpen] = useState(false);

// Context hooks
const { getCartCount, isLoggedIn, addToWishlist, removeFromWishlist } = useCart();
```

**API Calls**:
```javascript
GET /api/v1/products?featured=true&limit=6  // Featured
GET /api/v1/products  // All products
GET /api/v1/products/search?q=query  // Search
```

---

#### ProductsScreen.js
**Purpose**: Browse full product catalog with filters  
**Features**:
- Product grid (2 columns)
- Filter by category
- Sort by price/name/newest
- Search within category
- Infinite scroll / pagination
- Add to cart quick action
- Add to wishlist quick action

**Filter Logic**:
```javascript
const filters = {
  category: selectedCategory,
  search: searchQuery,
  sort_by: sortBy,  // price, name, created_at
  sort_order: sortOrder,  // asc, desc
  per_page: 12
}

GET /api/v1/products?category=1&sort_by=price
```

---

#### ProductDetailScreen.js
**Purpose**: Detailed product view  
**Navigation Flow**:
- Add to Cart → CartScreen
- Add to Wishlist → Stay on screen
- View Reviews → ReviewsScreen
- Tap related products → ProductDetailScreen

**Features**:
- Product image carousel
- Full description
- Price display
- Stock status
- Customer reviews & ratings
- Related products
- Add to cart / Add to wishlist buttons
- Quantity selector

**Data Structure**:
```javascript
const product = {
  id, name, description, price, stock,
  images: [url1, url2, ...],
  category: { id, name },
  reviews: [{ rating, comment, user, date }],
  average_rating, review_count,
  sku, featured
}
```

---

#### CartScreen.js
**Purpose**: Shopping cart review & management  
**Features**:
- List cart items with images
- Update quantity
- Remove items
- Clear entire cart
- Cart summary (subtotal, shipping, discount, total)
- Proceed to checkout button
- Continue shopping button

**Cart Context Methods**:
```javascript
const {
  cartItems,
  addToCart(product, quantity),
  updateCart(productId, quantity),
  removeFromCart(productId),
  clearCart(),
  getCartCount(),
  getCartTotal()
} = useCart();
```

**Storage**:
- Primary: AsyncStorage (Frontend)
- Optional: Server-side cart (future)

---

#### CheckoutScreen.js
**Purpose**: Order review & placement  
**Steps**:
1. Review cart items
2. Select delivery address
3. Select delivery type (pickup/deliver)
4. Select payment method
5. Enter customer info
6. Review order total
7. Place order

**Form Fields**:
```javascript
{
  customer_name: string,
  customer_email: string,
  customer_phone: string,
  shipping_address: string,
  delivery_address: string,
  delivery_type: "pickup" | "deliver",
  payment_method: "gcash" | "bank_transfer" | "cash",
  notes: string (optional)
}
```

**API Call**:
```javascript
POST /api/v1/orders
{
  ...order_data,
  items: cartItems,
  gcash_receipt?: File,
  bank_receipt?: File
}
```

---

#### PaymentScreen.js
**Purpose**: Payment method selection & proof upload  
**Features**:
- Payment method selection
  - GCash: Image proof upload
  - Bank Transfer: Receipt image upload
  - Cash on Delivery: No upload needed
- Payment proof image preview
- Confirmation before submission
- Upload status indicator

**File Upload Handling**:
```javascript
// Launch image picker
const { assets } = await ImagePicker.launchImageLibraryAsync({
  mediaTypes: ImagePicker.MediaType.IMAGES,
  aspect: [1, 1],
  quality: 0.8,
  base64: false
});

// Create FormData for multipart upload
const formData = new FormData();
formData.append('gcash_receipt', {
  uri: assets[0].uri,
  type: 'image/jpeg',
  name: 'receipt.jpg'
});

// Upload with order
POST /api/v1/orders { ...orderData, gcash_receipt: formData }
```

---

#### OrdersScreen.js
**Purpose**: User's order history  
**Features**:
- List all user orders
- Order status indicator (pending, confirmed, shipped, delivered)
- Order reference & date
- Total price
- Tap order → OrderDetailsScreen

**API Call**:
```javascript
GET /api/v1/orders
Response: [{ id, order_ref, tracking_number, total, status, date }]
```

---

#### OrderDetailsScreen.js
**Purpose**: Detailed order view & tracking  
**Features**:
- Order items displayed with images
- Customer information
- Shipping address
- Order summary (subtotal, shipping, tax, total)
- Payment method & status
- Tracking history timeline
- Current status with timestamp
- Estimated delivery date

**Real-Time Updates**:
```javascript
// Poll for updates every 5 seconds
useEffect(() => {
  const polling = setInterval(() => {
    fetchOrder(orderId);
  }, 5000);
  
  return () => clearInterval(polling);
}, [orderId]);

// Check status changes
if (previousStatus !== currentStatus) {
  showNotification(`Order ${newStatus}!`);
}
```

---

#### TrackOrderScreen.js
**Purpose**: Dedicated order tracking interface  
**Features**:
- Enter tracking number or order ID
- Real-time tracking status
- Delivery timeline visualization
- Current location (if available)
- Estimated delivery

---

### Feature Screens

#### CustomOrderScreen.js
**Purpose**: Request custom/bespoke Yakan textile orders  
**Navigation Flow**:
- Submit form → ChatScreen (for discussion)

**Form Sections**:
```javascript
// Section 1: Product Type
- Fabric type dropdown
- Intended use dropdown
- Quantity (meters)
- Specifications textarea

// Section 2: Customization
- Color selection (primary, secondary, accent)
- Pattern selection
- Special requirements

// Section 3: Design
- Upload design image/file
- Describe design

// Section 4: Timeline & Budget
- Expected completion date
- Budget range
- Urgency level

// Section 5: Contact
- Phone number
- Email
- Delivery address
```

**Form Validation**:
- All required fields filled
- Valid phone number format
- Valid file type for designs
- Budget within reason

**Submission**:
```javascript
POST /api/v1/chats
{
  topic: "custom_order",
  message: "Custom order inquiry",
  attachments: [designFile]
}
// Creates chat thread for admin-user discussion
```

---

#### ChatScreen.js
**Purpose**: Chat-based inquiry & custom order discussion  
**Features**:
- Message list with timestamps
- Sender identification (user/admin)
- Message input field
- Attachment upload
- Quote information display
- Status indicators (open/closed)
- Scroll to latest message

**Chat UI Structure**:
```javascript
<ScrollView>
  {messages.map(msg => (
    <ChatBubble
      sender={msg.sender}  // user | admin
      message={msg.message}
      timestamp={msg.created_at}
      attachments={msg.attachments}
      isQuote={msg.is_quote}
      quoteData={msg.quote}
    />
  ))}
</ScrollView>

<TextInput placeholder="Type message..." />
<Button onPress={uploadFile}>Attach File</Button>
<Button onPress={sendMessage}>Send</Button>
```

**API Calls**:
```javascript
GET /api/v1/chats/{id}  // Load chat
POST /api/v1/chats/{id}/messages  // Send message
POST /api/v1/chats/{id}/respond-quote  // Admin quote
PATCH /api/v1/chats/{id}/status  // Update status
```

---

#### CulturalHeritageScreen.js
**Purpose**: Educational content about Yakan textiles  
**Features**:
- Category list (origins, patterns, techniques, significance)
- Educational articles
- Photo gallery
- Historical information
- Pattern library with descriptions

**Content Structure**:
```javascript
{
  id, slug, title, description,
  image, category,
  content: {
    sections: [
      { heading, body, images }
    ]
  },
  releated_products: [{ id, name, price, image }]
}
```

---

#### WishlistScreen.js
**Purpose**: Saved favorite products  
**Features**:
- List of wishlist items
- Product images
- Product names & prices
- Remove from wishlist
- Add to cart from wishlist
- Share wishlist (optional)

**API Calls**:
```javascript
GET /api/v1/wishlist  // Fetch all
POST /api/v1/wishlist/remove  // Remove item
POST /api/v1/wishlist/add  // Add item
```

---

#### ReviewsScreen.js
**Purpose**: Product reviews & ratings  
**Features**:
- Reviews list with star ratings
- Reviewer names & dates
- Review text
- Filter by rating
- Submit review (if owner)
- Review sorting (newest, highest rated)

---

### Account Screens

#### AccountScreen.js
**Purpose**: User profile & account management  
**Navigation Flow**:
- Edit profile → Update user info
- Saved addresses → SavedAddressesScreen
- Payment methods → PaymentMethodsScreen
- Orders → OrdersScreen
- Wishlist → WishlistScreen
- Notifications → NotificationsScreen
- Settings → SettingsScreen
- Logout → LoginScreen

**Profile Display**:
```javascript
{
  avatar: imageUrl,
  name: "John Doe",
  email: "john@example.com",
  phone: "+63-9XX-XXX-XXXX",
  joinDate: "January 2026"
}
```

---

#### SavedAddressesScreen.js
**Purpose**: Manage delivery addresses  
**Features**:
- List all saved addresses
- Set default address
- Edit address
- Delete address
- Add new address form

**Address Data**:
```javascript
{
  id, label, street, city, province,
  postal_code, phone_number,
  is_default: boolean
}
```

**API Calls**:
```javascript
GET /api/v1/addresses
POST /api/v1/addresses
PUT /api/v1/addresses/{id}
DELETE /api/v1/addresses/{id}
POST /api/v1/addresses/{id}/set-default
```

---

#### PaymentMethodsScreen.js
**Purpose**: Manage payment methods  
**Features** (Future):
- Saved credit cards (if integrated)
- Digital wallet options
- Default payment method
- Add new payment method

---

#### NotificationsScreen.js
**Purpose**: Notification inbox  
**Features**:
- List all notifications
- Notification types: order status, payment, system
- Mark as read
- Delete notification
- Tap notification → relevant screen

**Notification Types**:
```javascript
{
  id, type: "order_status|payment|system",
  title, message, icon,
  related_entity: { type: "order", id: 123 },
  read_at: timestamp | null,
  created_at: timestamp
}
```

---

#### SettingsScreen.js
**Purpose**: App preferences & settings  
**Features**:
- Account settings
- Notification preferences (email, push)
- App theme (light/dark) - optional
- Language selection - future
- Privacy & security
- Help & support
- About app
- Logout button

---

---

## 🔧 Core Components (Reusable)

### BottomNav.js
**Purpose**: Bottom tab navigation for main screens  
**Tabs**:
```javascript
1. Home (HomeScreen)
2. Products (ProductsScreen)
3. Cart (CartScreen) - with count badge
4. Account (AccountScreen)
5. Menu (MenuScreen)
```

**Features**:
- Tab switching
- Cart count indicator
- Notification badge
- Inactive/active styling

**Implementation**:
```javascript
<View style={styles.bottomNav}>
  <Tab 
    icon="home"
    label="Home"
    isActive={activeTab === 'home'}
    onPress={() => navigation.navigate('Home')}
    badge={null}
  />
  <Tab 
    icon="shopping-cart"
    label="Cart"
    isActive={activeTab === 'cart'}
    onPress={() => navigation.navigate('Cart')}
    badge={cartCount > 0 ? cartCount : null}
  />
  {/* More tabs */}
</View>
```

---

### Header.js
**Purpose**: Custom header for screens  
**Props**:
```javascript
{
  title: string,
  subtitle?: string,
  leftButton?: { icon, onPress },
  rightButton?: { icon, onPress },
  showSearch?: boolean,
  onSearch?: function
}
```

---

### NotificationBar.js
**Purpose**: Toast-like notification display  
**Features**:
- Auto-dismiss after 3 seconds
- Supports success, error, info, warning types
- Positioned at top of screen
- Slide animation

**Usage**:
```javascript
const { showNotification } = useNotification();
showNotification("Order placed successfully!", "success");
```

---

### ErrorBoundary.js
**Purpose**: Catch React errors and prevent app crash  
**Features**:
- Error logging
- Fallback UI
- Retry button
- Error details (development mode)

---

### ShippingFeeCalculator.js
**Purpose**: Calculate shipping based on location  
**Logic**:
```javascript
{
  location: string,
  distance: number,
  weight: number
} -> shippingFee: number

// Example rates
const rates = {
  zamboanga_city: 150,
  nearby_provinces: 200,
  remote_areas: 500
}
```

---

### AdminOrderDashboard.js
**Purpose**: Admin panel for order management  
**Features** (Future):
- Order list with status
- Quick status update
- Order details modal
- Payment verification
- Customer contact

---

## 🎨 Styling & Colors

### Color Scheme (`src/constants/colors.js`)
```javascript
const colors = {
  primary: '#8B4513',        // Brown (Yakan theme)
  secondary: '#D4AF37',      // Gold
  accent: '#FF6B6B',         // Red for alerts
  
  background: '#FFFFFF',
  text: '#333333',
  textLight: '#666666',
  textMuted: '#999999',
  
  success: '#4CAF50',
  warning: '#FF9800',
  error: '#F44336',
  info: '#2196F3',
  
  border: '#EEEEEE',
  disabled: '#CCCCCC'
}
```

### Typography
```javascript
const typography = {
  h1: { fontSize: 32, fontWeight: 'bold' },
  h2: { fontSize: 24, fontWeight: 'bold' },
  h3: { fontSize: 18, fontWeight: 'bold' },
  body: { fontSize: 14, fontWeight: '400' },
  small: { fontSize: 12, fontWeight: '400' }
}
```

---

## 🔌 State Management (Context API)

### CartContext.js
**Purpose**: Global cart state management  
**Provider**: `<CartProvider>` wraps app in App.js

**Methods**:
```javascript
{
  // State
  cartItems: Array<CartItem>,
  
  // Actions
  addToCart(product, quantity): void,
  updateCart(productId, quantity): void,
  removeFromCart(productId): void,
  clearCart(): void,
  
  // Getters
  getCartCount(): number,
  getCartTotal(): number,
  isInWishlist(productId): boolean,
  addToWishlist(productId): void,
  removeFromWishlist(productId): void
}
```

**Implementation**:
```javascript
const CartContext = createContext();

export const CartProvider = ({ children }) => {
  const [cartItems, setCartItems] = useState([]);
  
  const addToCart = async (product, quantity) => {
    const updatedCart = [...cartItems];
    // Add or update item
    await AsyncStorage.setItem('cart', JSON.stringify(updatedCart));
    setCartItems(updatedCart);
  };
  
  return (
    <CartContext.Provider value={{ cartItems, addToCart, ... }}>
      {children}
    </CartContext.Provider>
  );
};

export const useCart = () => useContext(CartContext);
```

---

### NotificationContext.js
**Purpose**: Global notification system  
**Provider**: `<NotificationProvider>` wraps app in App.js

**Methods**:
```javascript
{
  showNotification(
    message: string,
    type: 'success' | 'error' | 'info' | 'warning',
    duration?: number
  ): void,
  clearNotification(): void
}
```

---

## 🔄 API Service Pattern

### ApiService (`src/services/api.js`)
**Wrapper** around Axios for consistent API calls

**Methods**:
```javascript
ApiService.request(
  method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE',
  endpoint: string,
  data?: object,
  options?: object
): Promise<ApiResponse>

// Response format
{
  success: boolean,
  message: string,
  data: any,
  error?: string
}
```

**Usage**:
```javascript
// GET request
const response = await ApiService.request('GET', '/products');

// POST request with data
const response = await ApiService.request('POST', '/orders', {
  customer_name: "John",
  items: [...]
});

// File upload
const response = await ApiService.request('POST', '/orders', {
  gcash_receipt: fileUri
}, { isMultipart: true });
```

**Features**:
- Automatic token attachment
- Request/response logging (development)
- Error handling
- Timeout management
- Retry logic (optional)

---

## 📦 Hooks (Custom)

### useOrderPolling
**Purpose**: Poll for order updates  
**Usage**:
```javascript
const { order, isLoading, error } = useOrderPolling(orderId, 5000);
```

---

## 🌐 Configuration (`src/config/config.js`)

```javascript
const config = {
  API_BASE_URL: __DEV__ 
    ? 'http://localhost:8000/api/v1'
    : 'https://api.yakan.com/api/v1',
  
  APP_VERSION: '1.0.0',
  POLLING_INTERVAL: 5000,  // 5 seconds
  
  PAYMENT_METHODS: {
    GCASH: 'gcash',
    BANK_TRANSFER: 'bank_transfer',
    CASH_ON_DELIVERY: 'cash'
  },
  
  ORDER_STATUSES: {
    PENDING: 'pending',
    CONFIRMED: 'confirmed',
    SHIPPED: 'shipped',
    DELIVERED: 'delivered',
    CANCELLED: 'cancelled'
  }
};
```

---

## 📱 Responsive Design

### Screen Size Handling
```javascript
import { Dimensions } from 'react-native';

const { width, height } = Dimensions.get('window');

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: width > 600 ? 20 : 10,  // Tablet vs mobile
  }
});
```

### Safe Area Handling
```javascript
import { SafeAreaView } from 'react-native-safe-area-context';

<SafeAreaView edges={['top', 'bottom']}>
  {/* Content */}
</SafeAreaView>
```

---

## 🔐 Security Best Practices

### Token Storage
```javascript
// Store securely in AsyncStorage
await AsyncStorage.setItem('authToken', token);

// Retrieve with error handling
const token = await AsyncStorage.getItem('authToken');

// Clear on logout
await AsyncStorage.removeItem('authToken');
await AsyncStorage.removeItem('userData');
```

### Input Validation
```javascript
// Email validation
const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

// Phone validation (Philippine format)
const isValidPhone = (phone) => {
  return /^(\+63|0)[0-9]{10}$/.test(phone);
};
```

---

## 🎯 Best Practices

### Component Structure
```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet } from 'react-native';

const MyScreen = ({ navigation, route }) => {
  // State
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  
  // Effects
  useEffect(() => {
    fetchData();
  }, []);
  
  // Methods
  const fetchData = async () => {
    // Implementation
  };
  
  // Render
  return (
    <View style={styles.container}>
      {loading ? <ActivityIndicator /> : <Text>{data}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: 16 }
});

export default MyScreen;
```

### Navigation Best Practices
```javascript
// Navigate with params
navigate('ProductDetail', { productId: 123 });

// Get route params
const { productId } = route.params;

// Go back
navigation.goBack();

// Reset stack
navigation.reset({
  index: 0,
  routes: [{ name: 'Home' }]
});
```

---

## 📊 Performance Optimization

### Image Optimization
```javascript
// Use cached images
<Image
  source={{ uri: imageUrl }}
  cache={'force-cache'}
  style={{ width: 200, height: 200 }}
/>
```

### FlatList Optimization
```javascript
<FlatList
  data={items}
  renderItem={({ item }) => <Item {...item} />}
  keyExtractor={item => item.id}
  removeClippedSubviews={true}
  maxToRenderPerBatch={10}
  updateCellsBatchingPeriod={50}
/>
```

### Memoization
```javascript
import { useMemo, useCallback } from 'react';

const MyComponent = ({ data = [] }) => {
  // Memoize expensive computation
  const filteredData = useMemo(() => {
    return data.filter(item => item.active);
  }, [data]);
  
  // Memoize callback
  const handlePress = useCallback(() => {
    // Handle action
  }, []);
};
```

---

## 🚀 Deployment Notes

### Build for Production
```bash
# Expo build for iOS
eas build --platform ios --auto-submit

# Expo build for Android
eas build --platform android

# Submit to app stores
eas submit --platform ios
eas submit --platform android
```

### Environment Configuration
```javascript
// Development
const API_BASE_URL = 'http://localhost:8000/api/v1';
const DEBUG = true;

// Production
const API_BASE_URL = 'https://api.yakan.com/api/v1';
const DEBUG = false;
```

---

**Document Version**: 1.0  
**Last Updated**: February 8, 2026  
**Framework**: React Native Expo v54  
**Target Platforms**: iOS, Android, Web
