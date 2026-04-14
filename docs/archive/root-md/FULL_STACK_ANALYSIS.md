# 🎨 YAKAN WebApp - Full Stack Analysis

**Project**: E-Commerce Platform for Traditional Filipino Textiles (Yakan Fabrics)  
**Type**: Hybrid Mobile + Web Application  
**Current Date**: February 8, 2026

---

## 📋 Executive Summary

**Yakan-WebApp** is a full-stack e-commerce application designed for selling traditional Filipino Yakan textiles and related products. It features:

- **Mobile-First**: React Native (Expo) app for iOS, Android, and Web
- **Backend**: Laravel 12 REST API with comprehensive business logic
- **Database**: MySQL for persistent data storage
- **Authentication**: Multi-provider (Email, Google OAuth, Facebook OAuth)
- **Specialized Features**: Custom order system, chat-based inquiry, real-time notifications

---

## 🏗️ Architecture Overview

```
┌────────────────────────────────────────────────────────────────┐
│                      CLIENT LAYER                               │
├────────────────────────────────────────────────────────────────┤
│  React Native (Expo) - iOS, Android, Web                        │
│  • Navigation Stack (React Navigation)                          │
│  • State Management (Context API)                               │
│  • HTTP Client (Axios)                                          │
└────────────────────────────────────────────────────────────────┘
                              ↓
┌────────────────────────────────────────────────────────────────┐
│                    API GATEWAY LAYER                            │
├────────────────────────────────────────────────────────────────┤
│  REST API (v1) - Laravel Routes                                 │
│  • Base: /api/v1                                                │
│  • Auth: Sanctum Token-based                                    │
│  • CORS: Enabled                                                │
└────────────────────────────────────────────────────────────────┘
                              ↓
┌────────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                             │
├────────────────────────────────────────────────────────────────┤
│  Laravel 12 Controllers & Services                              │
│  • Business Logic Implementation                                │
│  • Data Validation                                              │
│  • Payment Processing                                           │
│  • File Management                                              │
└────────────────────────────────────────────────────────────────┘
                              ↓
┌────────────────────────────────────────────────────────────────┐
│                    PERSISTENCE LAYER                            │
├────────────────────────────────────────────────────────────────┤
│  • MySQL Database (35+ Tables)                                  │
│  • Redis Caching (optional)                                     │
│  • File Storage (/storage)                                      │
└────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Frontend Architecture (React Native)

### Entry Points
- **[App.js](App.js)**: Main application entry point
- **[package.json](package.json)**: Dependencies and scripts
- **[app.json](app.json)**: Expo configuration (iOS, Android, Web)

### Directory Structure

```
src/
├── screens/              # 25+ UI screens
├── components/           # Reusable components
├── services/             # API & utility services
├── context/              # State management (Redux equivalent)
├── hooks/                # Custom React hooks
├── config/               # Configuration files
├── constants/            # Constants (colors, endpoints)
├── assets/               # Images, icons, fonts
```

### Key Screens (25 Total)

**Authentication**
- `LoginScreen.js` - Email/password login
- `RegisterScreen.js` - User registration
- `ForgotPasswordScreen.js` - Password recovery

**Shopping**
- `HomeScreen.js` - Product discovery & featured items
- `ProductsScreen.js` - Product listing with filters
- `ProductDetailScreen.js` - Detailed product view
- `CartScreen.js` - Shopping cart management
- `WishlistScreen.js` - Saved favorites

**Ordering & Checkout**
- `CheckoutScreen.js` - Order review & confirmation
- `PaymentScreen.js` - Payment method selection
- `PaymentMethodsScreen.js` - Saved payment methods
- `SavedAddressesScreen.js` - Shipping addresses

**Order Management**
- `OrdersScreen.js` - User's order history
- `OrderDetailsScreen.js` - Order details & tracking
- `TrackOrderScreen.js` - Real-time order tracking

**Features**
- `ChatScreen.js` - Chat with sellers (custom inquiries)
- `CustomOrderScreen.js` - Custom order creation
- `CulturalHeritageScreen.js` - Educational content
- `ReviewsScreen.js` - Product reviews

**Account**
- `AccountScreen.js` - User profile
- `SettingsScreen.js` - App settings
- `NotificationsScreen.js` - Notification inbox

### State Management

**CartContext.js**
- Manages cart items globally
- Methods: `addToCart()`, `removeFromCart()`, `getCartCount()`
- localStorage persistence

**NotificationContext.js**
- Real-time notifications
- Methods: `showNotification()`, `clearNotification()`
- Integration with order updates

### Services

**[services/api.js](src/services/api.js)**
- Axios HTTP client wrapper
- Base URL: `${API_BASE_URL}/api/v1`
- Automatic token attachment
- Error handling & retry logic

**[services/orderService.js](src/services/orderService.js)**
- Order creation & management
- Receipt upload handling
- Order status polling

**[services/orderPollingService.js](src/services/orderPollingService.js)**
- Real-time order status updates
- WebSocket alternative to polling

**[services/notificationService.js](src/services/notificationService.js)**
- Push notification handling
- Order status notifications

### Dependencies (Key)

```json
{
  "react": "^19.1.0",
  "react-native": "0.81.5",
  "expo": "~54.0.33",
  "@react-navigation/native": "^7.1.24",
  "axios": "^1.13.2",
  "@react-native-google-signin/google-signin": "^16.1.1",
  "expo-auth-session": "^7.0.10",
  "@react-native-async-storage/async-storage": "^2.2.0",
  "expo-image-picker": "~17.0.9",
  "react-native-safe-area-context": "~5.6.0",
  "react-native-screens": "~4.16.0"
}
```

---

## 🔌 Backend Architecture (Laravel)

### Configuration
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (token-based)
- **Authorization**: Policies & Gate system

### Key Configuration Files
- [config/app.php](config/app.php) - App configuration
- [config/database.php](config/database.php) - Database setup
- [.env.example](.env.example) - Environment variables template

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/              # API Controllers
│   │   ├── Admin/            # Admin Controllers
│   │   └── Auth/             # Authentication
│   ├── Middleware/           # HTTP Middleware
│   └── Requests/             # Form Requests
├── Models/                   # 35+ Eloquent Models
├── Services/                 # Business Logic
│   ├── Admin/
│   ├── CustomOrder/
│   ├── Notification/
│   ├── Payment/
│   └── Upload/
├── Observers/                # Model Observers
├── Events/                   # Events
├── Listeners/                # Event Listeners
├── Mail/                     # Mailable Classes
├── Console/                  # Artisan Commands
└── Providers/                # Service Providers

database/
├── migrations/               # 100+ Migrations
├── seeders/                  # Database Seeders
└── factories/                # Factory Classes

routes/
├── api.php                   # API Routes (v1)
├── web.php                   # Web Routes
├── admin_api.php            # Admin API Routes
└── auth.php                 # Auth Routes
```

### API Routes Structure

**Base URL**: `http://localhost:8000/api/v1`

#### Public Routes (No Auth Required)

```
POST   /login                          # Email login
POST   /register                       # User registration
POST   /login-guest                    # Guest checkout

POST   /auth/google                    # Google OAuth
POST   /auth/facebook                  # Facebook OAuth

GET    /products                       # List products (paginated, cached)
GET    /products/featured              # Featured products
GET    /products/{id}                  # Product details
GET    /products/search                # Search products

GET    /cultural-heritage              # Cultural content list
GET    /cultural-heritage/categories   # Heritage categories
GET    /cultural-heritage/{slug}       # Heritage details

GET    /orders                         # User's orders
GET    /orders/{id}                    # Order details
```

#### Authenticated Routes (Sanctum Token Required)

**Auth**
```
POST   /logout                         # User logout
GET    /user                          # Current user profile
```

**Orders**
```
POST   /orders                         # Create new order
POST   /orders/{id}/upload-receipt    # Upload payment proof
POST   /payments/upload-proof         # Mobile-specific proof upload
PATCH  /orders/{id}/status            # Admin: Update order status
GET    /admin/orders                  # Admin: List all orders
```

**Wishlist**
```
GET    /wishlist                       # Get wishlist items
POST   /wishlist/add                   # Add item
POST   /wishlist/remove                # Remove item
POST   /wishlist/check                 # Check if in wishlist
```

**Shopping Cart** (Session-based, optional DB)
```
GET    /cart                           # Get cart items
POST   /cart                           # Add to cart
PUT    /cart/{id}                      # Update quantity
DELETE /cart/{id}                      # Remove item
DELETE /cart                           # Clear cart
```

**Addresses**
```
GET    /addresses                      # List user addresses
GET    /addresses/default              # Get default address
POST   /addresses                      # Create address
PUT    /addresses/{id}                 # Update address
DELETE /addresses/{id}                 # Delete address
POST   /addresses/{id}/set-default     # Set as default
```

**Chat & Custom Orders**
```
GET    /chats                          # List chat threads
GET    /chats/{id}                     # Get chat details
POST   /chats                          # Start new chat
POST   /chats/{id}/messages            # Send message
POST   /chats/{id}/respond-quote       # Respond to quote
PATCH  /chats/{id}/status              # Update chat status
```

### API Controllers (15 Total)

**Authentication**
- `AuthController`: login, register, logout, user profile
- `SocialAuthController`: Google & Facebook OAuth integration

**Products & Content**
- `ProductController`: Product CRUD, search, featured items
- `CulturalHeritageController`: Educational content about Yakan textiles
- `ReviewController`: Product reviews & ratings

**Orders & Checkout**
- `OrderController`: Order creation, tracking, admin management
- `CartController`: Shopping cart operations
- `CheckoutController`: Checkout processing
- `PaymentController`: Payment proof upload & verification

**User Features**
- `AddressController`: Shipping address management
- `WishlistController`: Favorite items management
- `ChatController`: Chat with sellers about custom orders
- `CustomOrderController`: Custom/bespoke order management
- `NotificationController`: User notifications
- `TrackingController`: Order tracking

### Key Models (35 Total)

**Core Business Models**
```php
User                    # Customers, Admin
Product                 # Catalog items
Order                   # Standard orders from mobile
CustomOrder             # Bespoke/custom orders
Cart                    # Shopping cart session
Wishlist                # Favorite items
Category                # Product categories

Chat                    # Custom order inquiries
ChatMessage             # Messages in chats
ChatPayment             # Payment quotes for chat

Review                  # Product reviews
Address/UserAddress     # Shipping addresses
Coupon                  # Discount codes
Notification            # User notifications

YakanPattern            # Traditional patterns
FabricType              # Fabric types for customization
IntendedUse             # Product intended purposes
PatternFabricCompat     # Pattern-fabric combinations

Inventory               # Stock management
AdminNotification       # System notifications for admins
SystemSetting           # Configuration values
```

### Database Migrations (100+)

**Core Tables**
- `users` - User accounts with OAuth provider fields
- `products` - Product catalog with pricing
- `orders` - Order records with status tracking
- `order_items` - Line items in orders
- `categories` - Product categories

**Customization**
- `custom_orders` - Bespoke order records
- `yakan_patterns` - Pattern library
- `fabric_types` - Available fabric options
- `intended_uses` - Product use cases
- `pattern_fabric_compatibility` - Pattern-fabric rules

**Chat & Messaging**
- `chats` - Chat threads (inquiry-based)
- `chat_messages` - Individual messages
- `chat_payments` - Quote & payment tracking

**User Management**
- `addresses` - Shipping addresses
- `wishlists` - Favorite collections
- `wishlist_items` - Items in wishlist
- `reviews` - Product reviews
- `notifications` - User notifications

**Commerce**
- `carts` - Shopping carts (optional DB)
- `coupons` - Discount codes
- `coupon_redemptions` - Applied coupons
- `inventories` - Stock levels

**Admin & System**
- `admin_notifications` - Admin alerts
- `system_settings` - Configuration store
- `production_scheduler` - Production timeline
- `recent_views` - User browsing history

### Services (Layered Architecture)

**Admin Services** (`app/Services/Admin/`)
- Order management service
- Dashboard statistics
- Admin notifications

**Custom Order Services** (`app/Services/CustomOrder/`)
- Quote generation
- Status management
- Production scheduling

**Payment Services** (`app/Services/Payment/`)
- Payment verification
- Receipt validation
- Transaction logging

**Notification Services** (`app/Services/Notification/`)
- Email notifications
- SMS notifications (optional)
- Push notifications

**Upload Services** (`app/Services/Upload/`)
- Image processing
- File validation
- Storage management

**Other Services**
- `ReplicateService`: Data synchronization

### Authentication Flow

**Token-Based (Sanctum)**
```
1. User Login (POST /api/v1/login)
   ↓
2. Backend creates API token
   ↓
3. Token sent to client
   ↓
4. Client stores in AsyncStorage
   ↓
5. All requests: Authorization: Bearer {token}
   ↓
6. Middleware validates token (auth:sanctum)
```

**OAuth (Google/Facebook)**
```
1. Frontend: Google/Facebook SDK login
   ↓
2. Get ID token + user data
   ↓
3. POST /api/v1/auth/google (with id_token)
   ↓
4. Backend creates/links user, returns token
   ↓
5. Client uses token for authenticated requests
```

---

## 💾 Database Schema (Key Tables)

### Users Table
```sql
users
├── id (PK)
├── name, email, password
├── first_name, last_name
├── avatar (profile picture)
├── role (user/admin)
├── provider (google/facebook) - for OAuth
├── provider_id
├── email_verified_at
├── phone, address
└── timestamps
```

### Products Table
```sql
products
├── id (PK)
├── name, description, price
├── category_id (FK)
├── image, sku
├── stock, status (active/inactive)
├── featured (boolean)
└── timestamps
```

### Orders Table
```sql
orders
├── id (PK)
├── order_ref (unique)
├── tracking_number
├── user_id (FK)
├── customer_name, email, phone
├── shipping_address, delivery_address
├── subtotal, shipping_fee, discount, total
├── payment_method (gcash/bank_transfer/cash)
├── payment_status (pending/paid/verified)
├── payment_proof_path
├── status (pending/confirmed/shipped/delivered)
├── tracking_status, tracking_history
├── notes, admin_notes
├── confirmed_at, shipped_at, delivered_at
└── timestamps
```

### Custom Orders Table
```sql
custom_orders
├── id (PK)
├── user_id (FK)
├── chat_id (FK) - linked to chat inquiry
├── quantity, budget_range, expected_date
├── status (pending/approved/production/complete)
├── payment_status
├── fabric_type, fabric_weight_gsm
├── primary_color, secondary_color
├── design_upload (file path)
├── estimated_price, final_price
├── production_completed_at
├── delivered_at
└── timestamps
```

### Chats Table
```sql
chats
├── id (PK)
├── user_id (FK)
├── admin_id (FK)
├── topic (inquiry type)
├── status (open/closed)
└── timestamps

chat_messages
├── id (PK)
├── chat_id (FK)
├── sender_id (FK)
├── message_text
├── attachments
└── timestamps
```

---

## 🔐 Security Features

### Authentication
- ✅ Laravel Sanctum tokens (API authentication)
- ✅ Bcrypt password hashing
- ✅ OAuth2 integration (Google, Facebook)
- ✅ Email verification
- ✅ Token expiration & refresh

### Authorization
- ✅ Role-based access control (User/Admin)
- ✅ Policy-based authorization
- ✅ Gate-based permissions
- ✅ Middleware enforcement

### Data Protection
- ✅ HTTPS/SSL (production)
- ✅ CORS enabled (controlled origins)
- ✅ Input validation & sanitization
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ CSRF protection (web routes)

### Payment Security
- ✅ Payment proof upload (GCash receipts, bank transfers)
- ✅ Admin verification before order processing
- ✅ Payment status tracking
- ✅ Receipt encryption/storage in non-public directory

---

## 📦 Key Features

### 1. Product Catalog
- ✅ Featured products
- ✅ Search with filters
- ✅ Category browsing
- ✅ Product reviews & ratings
- ✅ Stock management
- ✅ Price caching

### 2. Shopping Cart
- ✅ Add/remove items
- ✅ Quantity management
- ✅ Persistent storage (AsyncStorage)
- ✅ Cart summary calculation
- ✅ Clear cart option

### 3. Order Management
- ✅ User orders history
- ✅ Real-time order tracking
- ✅ Order status updates (confirmed, shipped, delivered)
- ✅ Order notes & customer feedback
- ✅ Admin order dashboard
- ✅ Order receipt uploads

### 4. Payment System
- ✅ Multiple payment methods:
  - Bank transfer
  - GCash (Philippine payment service)
  - Cash on delivery
- ✅ Payment proof upload
- ✅ Admin payment verification
- ✅ Transaction logging

### 5. Custom Orders
- ✅ Bespoke textile design requests
- ✅ Fabric customization options
- ✅ Chat-based inquiry system
- ✅ Quote generation & approval
- ✅ Production tracking
- ✅ Design file uploads

### 6. Chat System
- ✅ Real-time messaging UI
- ✅ Customer-Admin communication
- ✅ Chat attached to custom orders
- ✅ Quote exchange
- ✅ Status notifications

### 7. User Profiles
- ✅ Profile information
- ✅ Saved addresses (multiple)
- ✅ Order history
- ✅ Wishlist management
- ✅ Settings & preferences

### 8. Wishlist
- ✅ Add/remove favorite items
- ✅ Check if item in wishlist
- ✅ Persistent storage

### 9. Cultural Heritage
- ✅ Educational content about Yakan textiles
- ✅ Pattern library
- ✅ Historical information
- ✅ Category-based organization

### 10. Notifications
- ✅ Order status updates
- ✅ Payment confirmations
- ✅ Chat messages
- ✅ System alerts
- ✅ Real-time push notifications

### 11. Admin Dashboard
- ✅ Order management
- ✅ Order status updates
- ✅ Admin notifications
- ✅ Statistics & analytics

---

## 🚀 Deployment & DevOps

### Development Stack
- **Local**: XAMPP (Apache, MySQL, PHP)
- **Package Managers**: npm (Node), Composer (PHP)
- **Version Control**: Git

### Production Platforms
- **Railway.app**: Primary cloud hosting
- **Expo**: Mobile app distribution
- **EAS Build**: Native mobile builds

### Deployment Files
- `Procfile` - Heroku/Railway configuration
- `nixpacks.toml` - Railway build configuration
- `eas.json` - Expo EAS Configuration
- `railway.json` - Railway configuration
- Deployment scripts: `deploy.bat`, `deploy-mobile.ps1`

### Database
- MySQL 8.0+ (production)
- SQLite (optional testing/development)

---

## 📊 Statistics

| Aspect | Count |
|--------|-------|
| Frontend Screens | 25 |
| API Controllers | 15 |
| Models | 35 |
| Database Migrations | 100+ |
| API Routes | 50+ |
| Services | 8+ |
| Components | 6+ |
| Dependencies (npm) | 20+ |
| Dependencies (composer) | 10+ |

---

## ⚠️ Current Status & Known Issues

### Completed Features ✅
- Authentication (email, Google, Facebook)
- Product catalog & browsing
- Shopping cart
- Order placement & tracking
- Custom orders with chat system
- Payment proof upload
- User addresses management
- Wishlist functionality
- Notifications system

### In Progress 🔄
- Real-time WebSocket integration (vs polling)
- Admin dashboard refinement
- Mobile responsiveness optimization
- Performance optimization (caching strategies)

### Known Limitations ⚠️
- Cart stored in AsyncStorage (not synced to server)
- Payment processing requires manual admin verification
- No automated SMS notifications (configured for email)
- Chat system is inquiry-based, not real-time messaging

---

## 📝 Environment Variables

### Required (.env)
```bash
APP_NAME=Yakan
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yakan_db
DB_USERNAME=root
DB_PASSWORD=password

# OAuth
GOOGLE_CLIENT_ID=xxx
GOOGLE_CLIENT_SECRET=xxx
FACEBOOK_APP_ID=xxx
FACEBOOK_APP_SECRET=xxx

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=app-password

# API
API_BASE_URL=https://your-domain.com/api/v1
```

---

## 🔗 Key Dependencies

### Frontend (React Native)
- `expo` - Cross-platform framework
- `react-navigation` - Navigation library
- `axios` - HTTP client
- `@react-native-google-signin` - Google login
- `expo-image-picker` - Image selection
- `async-storage` - Local persistence

### Backend (Laravel)
- `laravel/framework` v12
- `laravel/sanctum` - API authentication
- `laravel/socialite` - OAuth integration
- `doctrine/dbal` - Database abstraction

---

## 🎯 Next Steps & Recommendations

### Short Term
1. Implement WebSocket for real-time chat
2. Add payment gateway integration (Paymongo, Stripe)
3. Improve admin dashboard UX
4. Add inventory management system
5. Implement automated email notifications

### Medium Term
1. Server-side cart synchronization
2. Advanced analytics & reporting
3. Multi-language support (Tagalog, English)
4. Mobile-optimized admin panel
5. Bulk order management

### Long Term
1. AI-powered recommendations
2. Subscription/pre-order system
3. Marketplace (multiple sellers)
4. Inventory forecasting
5. Mobile app offline mode

---

## 📞 Support & Documentation

### Documentation Files
- [SETUP_INSTRUCTIONS.md](SETUP_INSTRUCTIONS.md) - Initial setup guide
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment procedures
- [ARCHITECTURE.md](ARCHITECTURE.md) - Architecture details
- [DATABASE_SETUP.md](DATABASE_SETUP.md) - Database initialization
- [MOBILE_APP_DEPLOYMENT.md](MOBILE_APP_DEPLOYMENT.md) - Mobile build guide

### Configuration Guides
- [FACEBOOK_SETUP_COMPLETE.md](FACEBOOK_SETUP_COMPLETE.md) - Facebook OAuth setup
- [GMAIL_SETUP_INSTRUCTIONS.md](GMAIL_SETUP_INSTRUCTIONS.md) - Email configuration
- [RAILWAY_DEPLOYMENT.md](RAILWAY_DEPLOYMENT.md) - Railway.app deployment

---

## 📄 License

This project is built with Laravel (MIT License) and React Native (MIT License).

---

**Last Updated**: February 8, 2026  
**Maintained By**: Development Team  
**Status**: Production Ready with Ongoing Enhancements
