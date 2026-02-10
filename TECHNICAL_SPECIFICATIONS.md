# 🔧 YAKAN WebApp - Technical Specifications & Data Flow

**Document**: Detailed Technical Reference  
**Version**: 1.0  
**Last Updated**: February 8, 2026

---

## 📡 Data Flow Diagrams

### 1. User Registration & Authentication Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    REGISTRATION FLOW                                 │
└─────────────────────────────────────────────────────────────────────┘

Mobile App (Frontend):
  [RegisterScreen.js]
      ↓
  Validate form inputs
      ↓
  POST /api/v1/register
  {
    name: string,
    email: string,
    password: string (hashed on frontend)
  }
      ↓
Backend (Laravel):
  [AuthController::register()]
      ↓
  Validate request (email unique, password strong)
      ↓
  Hash password with bcrypt
      ↓
  Create User record
  - Hash password
  - Generate verification token (optional)
      ↓
  Send verification email (optional)
      ↓
  Generate API token (Sanctum)
      ↓
  Response: 201 Created
  {
    success: true,
    data: {
      user: { id, name, email, role },
      token: "sanctum-token-xxx"
    }
  }
      ↓
Frontend:
  Store token in AsyncStorage
  Store user data in Context
  Navigate to HomeScreen
```

### 2. Product Browse & Add to Cart Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│              PRODUCT SEARCH & CART ADDITION FLOW                     │
└─────────────────────────────────────────────────────────────────────┘

HomeScreen (Frontend):
  useEffect()
      ↓
  GET /api/v1/products?featured=true
      ↓
Backend:
  [ProductController::index()]
      ↓
  Query Products (cached for 3600s)
  - Filter by featured
  - Load relationships (category)
  - Format response
      ↓
  Response: 200 OK
  {
    success: true,
    data: [
      {
        id, name, price, image,
        category: { id, name },
        stock, featured
      }
    ]
  }
      ↓
Frontend:
  [transformProducts()] - Image URL mapping
      ↓
  setFeaturedProducts(products)
      ↓
  Render in ScrollView
      ↓
User clicks "Add to Cart"
      ↓
  CartContext::addToCart()
      ↓
  Update cart state
  Save to AsyncStorage
      ↓
  Show notification
```

### 3. Order Placement & Payment Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│              ORDER CREATION & PAYMENT VERIFICATION                   │
└─────────────────────────────────────────────────────────────────────┘

CheckoutScreen (Frontend):
  1. User reviews cart items
  2. User selects delivery address
  3. User selects payment method
  4. User enters shipping info
      ↓
  POST /api/v1/orders
  {
    customer_phone: string,
    shipping_address: string,
    delivery_address: string,
    payment_method: "gcash|bank_transfer|cash",
    subtotal: number,
    shipping_fee: number,
    discount: number,
    total: number,
    items: [
      { product_id, quantity, price },
      ...
    ],
    gcash_receipt: File (optional),
    bank_receipt: File (optional)
  }
      ↓
Backend:
  [OrderController::store()]
      ↓
  Authenticate user (must be logged in)
  Validate order data
      ↓
  Create Order record
  - Generate order_ref (YAKAN-XXXX)
  - Generate tracking_number
  - Store payment_method
  - Store payment status: 
    • "pending" (if GCash/Bank)
    • "pending" (if Cash on Delivery)
      ↓
  Create OrderItems (one per product)
  - Store product_id, quantity, price
      ↓
  Update Product stock (decrement)
      ↓
  Handle file uploads:
  - Store GCash receipt → /storage/receipts/
  - Store Bank receipt → /storage/receipts/
      ↓
  Verify stock availability
      ↓
  Send confirmation email
      ↓
  Response: 201 Created
  {
    success: true,
    data: {
      order_id, order_ref, tracking_number,
      status: "pending",
      total, payment_method,
      customer_info: { phone, email, name }
    }
  }
      ↓
Frontend:
  Navigate to OrderDetailsScreen
  Show "Order placed successfully"
  Display order tracking number
  Start order polling
      ↓
Admin Panel:
  Receives notification of new order
  Reviews payment proof
  Verifies payment
      ↓
  PATCH /api/v1/orders/{id}/status
  {
    status: "confirmed",
    admin_notes: "Payment verified"
  }
      ↓
Database:
  Update Order.payment_status = "verified"
  Update Order.status = "confirmed"
      ↓
Frontend (Polling):
  GET /api/v1/orders/{id}
  Detects status change
      ↓
  NotificationContext::showNotification()
  "Your order has been confirmed!"
```

### 4. Custom Order (Chat-Based) Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│            CUSTOM ORDER WITH CHAT & QUOTE SYSTEM                     │
└─────────────────────────────────────────────────────────────────────┘

CustomOrderScreen (Frontend):
  1. User fills custom order form
     - Fabric type, color, specifications
     - Upload design image
     - Set budget & timeline
      ↓
  POST /api/v1/chats
  {
    topic: "custom_order",
    message: "I want custom Yakan fabric..."
  }
      ↓
Backend:
  [ChatController::store()]
      ↓
  Create Chat record
  - user_id, admin_id (assigned)
  - status: "open"
  - topic: "custom_order"
      ↓
  Create initial ChatMessage
      ↓
  Response: Chat object with messages
      ↓
Frontend:
  Navigate to ChatScreen with chat_id
      ↓
User types message:
  POST /api/v1/chats/{id}/messages
  {
    message: "Can you provide a quote?",
    attachments: File[] (design files)
  }
      ↓
Backend:
  [ChatController::sendMessage()]
      ↓
  Store ChatMessage
  Handle file uploads (temporary)
  Trigger admin notification
      ↓
Admin (Web Panel):
  Sees new chat message
  Reviews requirements
  Creates quote
      ↓
  POST /api/v1/chats/{id}/respond-quote
  {
    message: "Quote for your custom order...",
    estimated_price: 5000,
    expected_production_date: "2026-02-20",
    estimated_production_days: 7
  }
      ↓
Backend:
  Create ChatMessage with quote details
  Create CustomOrder record
  - Link to chat_id
  - status: "pending" (awaiting user approval)
  - estimated_price
      ↓
Frontend (Real-time):
  PATCH /api/v1/chats/{id}/status
  {
    last_message_status: "quote_received"
  }
      ↓
  NotificationContext shows: "Quote received! ₱5000"
      ↓
User reviews quote:
  If approved:
    Update CustomOrder.status = "approved"
    Upload payment or proceed to payment
  If rejected:
    Send counter-offer message
```

### 5. Real-Time Notifications Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│              REAL-TIME ORDER STATUS NOTIFICATIONS                    │
└─────────────────────────────────────────────────────────────────────┘

Option A: Polling (Current Implementation)
────────────────────────────────────────────

Frontend:
  [orderPollingService.js]
      ↓
  setInterval(() => {
    GET /api/v1/orders/{id}
  }, 5000)  // Every 5 seconds
      ↓
  Compare previous status with new status
  If changed:
    NotificationContext::showNotification()
    Play sound
    Update OrderDetailsScreen


Option B: WebSocket (Future Implementation)
──────────────────────────────────────────

Frontend:
  ws://api.yakan.com/ws/orders/{id}
      ↓
Backend:
  [WebSocket Server - Laravel Echo]
      ↓
  Listen for order updates
  Broadcast to specific user
      ↓
  When admin updates order:
    Broadcast('order.updated', order)
      ↓
Frontend:
  Receives real-time event
  Update UI instantly
  Show notification
```

---

## 📋 API Specification Details

### Authentication Endpoints

#### POST /api/v1/register
**Description**: Create new user account  
**Auth Required**: No  
**Request**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123!"
}
```
**Response** (201):
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|token-here-xxx"
  }
}
```

#### POST /api/v1/login
**Description**: Login with email/password  
**Auth Required**: No  
**Request**:
```json
{
  "email": "john@example.com",
  "password": "SecurePassword123!"
}
```
**Response** (200):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|token-here-xxx"
  }
}
```

#### POST /api/v1/auth/google
**Description**: OAuth login via Google  
**Auth Required**: No  
**Request**:
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IiJ9...",
  "email": "john@gmail.com",
  "name": "John Doe",
  "google_id": "118394927384923",
  "photo": "https://lh3.googleapis.com/a/..."
}
```
**Response** (200): Same as login response

#### POST /api/v1/auth/facebook
**Description**: OAuth login via Facebook  
**Auth Required**: No  
**Request**:
```json
{
  "access_token": "token-from-facebook",
  "email": "john@facebook.com",
  "name": "John Doe",
  "facebook_id": "123456789",
  "photo": "https://facebook.com/..."
}
```
**Response** (200): Same as login response

### Product Endpoints

#### GET /api/v1/products
**Description**: List all products with pagination & filtering  
**Auth Required**: No  
**Query Parameters**:
```
- category: integer (category ID)
- search: string (product name or description)
- featured: boolean
- sort_by: "name|price|created_at"
- sort_order: "asc|desc"
- per_page: integer (default: 12)
- limit: integer (override per_page)
```
**Example**: `GET /api/v1/products?category=1&sort_by=price&sort_order=asc&limit=20`  
**Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Yakan Handwoven Fabric",
      "description": "Traditional pattern...",
      "price": 500.00,
      "category": {
        "id": 1,
        "name": "Fabrics",
        "slug": "fabrics"
      },
      "image": "/storage/products/fabric-1.jpg",
      "stock": 50,
      "featured": true,
      "sku": "YAKAN-001"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 12
  }
}
```

#### GET /api/v1/products/{id}
**Description**: Get detailed product information  
**Auth Required**: No  
**Response** (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Yakan Handwoven Fabric",
    "description": "Traditional pattern...",
    "price": 500.00,
    "cost": 250.00,
    "category": { "id": 1, "name": "Fabrics" },
    "images": [
      "/storage/products/fabric-1.jpg",
      "/storage/products/fabric-2.jpg"
    ],
    "stock": 50,
    "featured": true,
    "sku": "YAKAN-001",
    "reviews": [
      {
        "id": 1,
        "rating": 5,
        "comment": "Excellent quality!",
        "user": { "name": "Maria" },
        "created_at": "2026-01-15T10:30:00Z"
      }
    ],
    "average_rating": 4.8,
    "review_count": 25
  }
}
```

### Order Endpoints

#### POST /api/v1/orders
**Description**: Create new order  
**Auth Required**: Yes (Sanctum)  
**Request**:
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+63-9XX-XXX-XXXX",
  "shipping_address": "123 Main Street, Zamboanga City",
  "delivery_address": "123 Main Street, Zamboanga City",
  "delivery_type": "deliver",
  "payment_method": "gcash",
  "payment_status": "pending",
  "payment_reference": "REF-123456",
  "subtotal": 1000.00,
  "shipping_fee": 150.00,
  "discount": 50.00,
  "total": 1100.00,
  "notes": "Please handle with care",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 500.00
    },
    {
      "product_id": 2,
      "quantity": 1,
      "price": 0.00
    }
  ]
}
```
**File Upload** (multipart/form-data):
```
- gcash_receipt: File (image/jpeg)
- bank_receipt: File (image/jpeg)
```
**Response** (201):
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 123,
    "order_ref": "YAKAN-20260208-0001",
    "tracking_number": "YK20260208000001",
    "user_id": 5,
    "customer_phone": "+63-9XX-XXX-XXXX",
    "subtotal": 1000.00,
    "shipping_fee": 150.00,
    "discount": 50.00,
    "total": 1100.00,
    "status": "pending",
    "payment_method": "gcash",
    "payment_status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 500.00
      }
    ]
  }
}
```

#### GET /api/v1/orders
**Description**: List user's orders  
**Auth Required**: Yes  
**Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "order_ref": "YAKAN-20260208-0001",
      "tracking_number": "YK20260208000001",
      "total": 1100.00,
      "status": "confirmed",
      "payment_status": "verified",
      "created_at": "2026-02-08T10:30:00Z",
      "confirmed_at": "2026-02-08T11:00:00Z"
    }
  ]
}
```

#### GET /api/v1/orders/{id}
**Description**: Get order details & tracking  
**Auth Required**: Yes  
**Response** (200):
```json
{
  "success": true,
  "data": {
    "id": 123,
    "order_ref": "YAKAN-20260208-0001",
    "tracking_number": "YK20260208000001",
    "customer_phone": "+63-9XX-XXX-XXXX",
    "shipping_address": "123 Main Street, Zamboanga City",
    "delivery_address": "123 Main Street, Zamboanga City",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "name": "Yakan Handwoven Fabric",
        "quantity": 2,
        "price": 500.00,
        "total": 1000.00
      }
    ],
    "subtotal": 1000.00,
    "shipping_fee": 150.00,
    "discount": 50.00,
    "total": 1100.00,
    "status": "confirmed",
    "payment_method": "gcash",
    "payment_status": "verified",
    "payment_verified_at": "2026-02-08T11:00:00Z",
    "tracking_status": "in_transit",
    "tracking_history": [
      {
        "status": "confirmed",
        "timestamp": "2026-02-08T11:00:00Z",
        "note": "Order confirmed by admin"
      },
      {
        "status": "shipment_ready",
        "timestamp": "2026-02-08T15:30:00Z",
        "note": "Ready for shipping"
      },
      {
        "status": "in_transit",
        "timestamp": "2026-02-09T08:00:00Z",
        "note": "Package on the way"
      }
    ],
    "confirmed_at": "2026-02-08T11:00:00Z",
    "shipped_at": "2026-02-09T08:00:00Z",
    "delivered_at": null
  }
}
```

#### PATCH /api/v1/orders/{id}/status
**Description**: Update order status (Admin only)  
**Auth Required**: Yes (Admin role)  
**Request**:
```json
{
  "status": "shipped",
  "admin_notes": "Package handed to courier"
}
```
**Response** (200):
```json
{
  "success": true,
  "message": "Order status updated",
  "data": {
    "id": 123,
    "status": "shipped",
    "admin_notes": "Package handed to courier",
    "shipped_at": "2026-02-09T08:00:00Z"
  }
}
```

### Wishlist Endpoints

#### GET /api/v1/wishlist
**Description**: Get user's wishlist  
**Auth Required**: Yes  
**Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "product": {
        "id": 1,
        "name": "Yakan Handwoven Fabric",
        "price": 500.00,
        "image": "/storage/products/fabric-1.jpg"
      }
    }
  ]
}
```

#### POST /api/v1/wishlist/add
**Description**: Add product to wishlist  
**Auth Required**: Yes  
**Request**:
```json
{
  "product_id": 1
}
```
**Response** (201):
```json
{
  "success": true,
  "message": "Added to wishlist",
  "data": { "id": 1, "product_id": 1 }
}
```

### Chat Endpoints

#### GET /api/v1/chats
**Description**: List user's chat threads  
**Auth Required**: Yes  
**Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "topic": "custom_order",
      "status": "open",
      "created_at": "2026-02-08T10:30:00Z",
      "messages_count": 5,
      "last_message": {
        "id": 5,
        "message": "Quote for your custom order...",
        "created_at": "2026-02-08T11:15:00Z"
      }
    }
  ]
}
```

#### POST /api/v1/chats/{id}/messages
**Description**: Send message in chat  
**Auth Required**: Yes  
**Request** (multipart/form-data):
```
- message: string (required)
- attachments: File[] (optional)
```
**Response** (201):
```json
{
  "success": true,
  "data": {
    "id": 6,
    "chat_id": 1,
    "sender": { "id": 5, "name": "John Doe" },
    "message": "User's message",
    "attachments": [],
    "created_at": "2026-02-08T12:00:00Z"
  }
}
```

---

## 🔄 Request/Response Patterns

### Success Response Format
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* payload */ }
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Operation failed",
  "error": "Detailed error message",
  "errors": {
    /* Validation errors by field */
  }
}
```

### Status Codes Used
- **200 OK**: Successful GET, PUT, PATCH
- **201 Created**: Successful POST (resource created)
- **400 Bad Request**: Validation error
- **401 Unauthorized**: Missing/invalid authentication token
- **403 Forbidden**: Authenticated but not authorized
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **500 Internal Server Error**: Server error

---

## 📦 Caching Strategy

### Product Caching
```php
// Cache products for 3600 seconds (1 hour)
$cacheKey = 'products:' . md5(json_encode($request->all()));
Cache::remember($cacheKey, env('PRODUCT_CACHE_TTL', 3600), function () {
  // Query products
});
```

### Cache Invalidation
Cache is invalidated when:
- Product is created/updated/deleted
- Stock changes significantly
- Manual cache clear command

---

## 🔐 Token Management

### Token Structure (Sanctum)
```
Format: {user_id}|{hashed_token}
Example: 1|ABC123DEF456...
Expiration: Can be configured
Revocation: Manual logout required
```

### Token Usage
```javascript
// Frontend
const token = await AsyncStorage.getItem('authToken');
const config = {
  headers: {
    'Authorization': `Bearer ${token}`
  }
};
axios.get('/api/v1/user', config);
```

---

## 📊 Database Relationships

### Key Relationships
```
User → Orders (1:many)
User → Wishlists (1:many)
User → Addresses (1:many)
User → Chats (1:many)
User → CustomOrders (1:many)

Order → OrderItems (1:many)
OrderItem → Product (many:1)

Product → Category (many:1)
Product → Reviews (1:many)
Product → WishlistItems (1:many)

Chat → ChatMessages (1:many)
Chat → CustomOrder (1:1)
ChatMessage → User (belongs to sender)

CustomOrder → Chat (belongs to)
CustomOrder → User (belongs to)

Category → Products (1:many)
```

---

## 🚀 Performance Considerations

### Optimization Techniques Used
1. **Product Caching**: 1-hour cache for product listings
2. **Eager Loading**: Load relationships in queries
3. **Pagination**: Limited results per page
4. **Image Optimization**: Stored in /storage (lazy loaded)
5. **Async Storage**: Frontend caching of user data

### Potential Optimizations
1. Implement Redis caching for frequently accessed data
2. Database query optimization (indexes on frequently filtered columns)
3. Image CDN integration
4. GraphQL API alternative to REST
5. Database connection pooling

---

## 🔗 Integration Points

### External Services Integration
1. **Google OAuth**: Integrated via `GoogleSignIntegration`
2. **Facebook OAuth**: Integrated via `FacebookAuthIntegration`
3. **Email Service**: Laravel Mail (SMTP)
4. **File Storage**: Local (/storage) or AWS S3
5. **Payment Processing**: Manual verification (ready for Paymongo/Stripe)

### Frontend-Backend Communication
```
Protocol: HTTPS (Production) / HTTP (Development)
Format: JSON
Headers:
  - Content-Type: application/json
  - Authorization: Bearer {token}
  - Accept: application/json
```

---

## 📝 Error Handling

### Frontend Error Handling
```javascript
// API Service wrapper
try {
  const response = await ApiService.request(method, endpoint, data);
  if (response.success) {
    // Handle success
  } else {
    // Handle API error
    showNotification(response.message, 'error');
  }
} catch (error) {
  // Handle network error
  console.error('Network error:', error);
}
```

### Backend Error Handling
```php
// Exception handling with detailed messages
try {
  // Business logic
} catch (\Exception $e) {
  Log::error('Operation failed', [
    'message' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
  ]);
  
  return response()->json([
    'success' => false,
    'message' => 'Operation failed',
    'error' => $e->getMessage()
  ], 500);
}
```

---

## ✅ Testing Recommendations

### Unit Tests
```bash
# Run Laravel tests
php artisan test

# Run specific test
php artisan test --filter=OrderControllerTest
```

### API Testing
```bash
# Using test scripts provided
./test_api.ps1          # Windows PowerShell
./test_api.sh           # Linux/Mac
```

### Frontend Testing
```bash
# Jest/React Native Testing
npm run test

# Lint code
npm run lint

# Fix linting issues
npm run lint:fix
```

---

**Document Version**: 1.0  
**Last Updated**: February 8, 2026  
**Prepared for**: Development & DevOps Teams
