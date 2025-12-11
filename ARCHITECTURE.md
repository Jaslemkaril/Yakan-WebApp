# 📐 YAKAN Order System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      YAKAN ORDER MANAGEMENT                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌──────────────┐      ┌──────────────┐      ┌──────────────┐  │
│   │  📱 MOBILE   │      │   🖥️ BACKEND   │      │  🌐 ADMIN      │
│   │   (Expo)     │      │   (Laravel)  │      │  (Web)       │
│   └──────────────┘      └──────────────┘      └──────────────┘  │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Components Architecture

```
MOBILE APP (React Native/Expo)
═══════════════════════════════════════════════════════════════

src/
├── screens/
│   ├── HomeScreen.js
│   ├── ProductsScreen.js
│   ├── CartScreen.js
│   ├── CheckoutScreen.js
│   │   └─ Shipping address
│   │   └─ Delivery options
│   │
│   └── PaymentScreen.js ⭐ UPDATED
│       ├─ Select payment method
│       ├─ Confirm payment
│       │
│       └─→ ApiService.createOrder()
│           ├─ POST /api/v1/orders
│           ├─ Get response with order ID
│           │
│           └─→ NotificationService.notifyNewOrder()
│               ├─ Emit 'newOrderCreated' event
│               ├─ Admin receives notification
│               │
│               └─→ NotificationService.startOrderStatusPolling()
│                   ├─ Poll every 15 seconds
│                   ├─ GET /api/v1/orders/{id}
│                   ├─ Check for status changes
│                   │
│                   └─→ OrderDetailsScreen
│                       └─ Show updated status ✓
│
├── services/
│   ├── api.js ✅ Already integrated
│   │   └─ ApiService.createOrder()
│   │   └─ ApiService.getOrder()
│   │
│   └── notificationService.js ⭐ NEW
│       ├─ startOrderStatusPolling()
│       ├─ notifyNewOrder()
│       ├─ onNewOrder()
│       ├─ onOrderStatusChange()
│       └─ cacheOrderUpdate()
│
└── components/
    ├── AdminOrderDashboard.js ⭐ NEW
    │   ├─ Real-time notification listener
    │   ├─ Order list display
    │   ├─ Filter & search
    │   ├─ Action buttons
    │   │   ├─ ✓ Confirm Order
    │   │   ├─ ⚙ Processing
    │   │   └─ 👁 View Details
    │   │
    │   └─→ fetch('/api/v1/admin/orders')
    │       └─→ patch('/api/v1/admin/orders/{id}/status')
```

## Backend Architecture

```
LARAVEL API (Backend)
═══════════════════════════════════════════════════════════════

routes/api.php
├─ POST   /api/v1/orders
├─ GET    /api/v1/orders
├─ GET    /api/v1/orders/{id}
├─ GET    /api/v1/admin/orders
└─ PATCH  /api/v1/admin/orders/{id}/status

app/Http/Controllers/OrderController.php ⭐ NEW
├─ store()              → Create order from mobile
├─ index()              → Get user's orders
├─ show()               → Get single order
├─ adminIndex()         → Get all orders (admin)
└─ updateStatus()       → Update order status (admin)
    ├─ Validate input
    ├─ Update order in DB
    ├─ Update timestamp (confirmed_at, shipped_at, etc)
    └─→ event(new OrderStatusChanged($order))

app/Models/
├─ Order.php ⭐ NEW
│  ├─ Relationships
│  │  ├─ user()
│  │  └─ items()
│  ├─ Mutators
│  │  └─ getStatusLabelAttribute()
│  ├─ Scopes
│  │  ├─ pending()
│  │  ├─ recent()
│  │  └─ withStatus()
│  └─ Static Methods
│     └─ generateOrderRef()
│
└─ OrderItem.php ⭐ NEW
   ├─ product_id, product_name
   ├─ quantity, price, total
   └─ Relationship: order()

app/Events/ ⭐ NEW
├─ OrderCreated.php
│  ├─ Triggered when order created
│  ├─ Broadcasts to 'orders' channel
│  ├─ Sends to admin dashboard
│  └─→ AdminOrderDashboard receives notification 🔔
│
└─ OrderStatusChanged.php
   ├─ Triggered when admin updates status
   ├─ Broadcasts to 'orders.{id}' channel
   ├─ Sends to mobile app
   └─→ Mobile polling receives update 📲

database/
├─ migrations/2024_12_11_create_orders_table.php ⭐ NEW
│  ├─ CREATE TABLE orders
│  │  ├─ id, order_ref (unique)
│  │  ├─ customer_name, customer_phone
│  │  ├─ subtotal, shipping_fee, discount, total
│  │  ├─ payment_method, payment_status
│  │  ├─ status (enum)
│  │  ├─ indexes on (status, payment_status, created_at)
│  │  └─ timestamps
│  │
│  └─ CREATE TABLE order_items
│     ├─ id, order_id (FK), product_id
│     ├─ quantity, price, total
│     └─ NO timestamps
```

## Data Flow Diagram

```
1. MOBILE APP - ORDER PLACEMENT
═══════════════════════════════════════════════════════════════

User adds items
    ↓
CartScreen (show items & total)
    ↓
CheckoutScreen (fill shipping address)
    ↓
PaymentScreen (select payment method)
    ↓
User clicks "Confirm Payment"
    ↓
    REQUEST: POST /api/v1/orders
    ────────────────────────→
    
    {
      customer_name: "John Doe",
      customer_phone: "09171234567",
      shipping_address: "123 Main St, Manila",
      payment_method: "gcash",
      items: [{product_id, quantity, price}],
      subtotal: 1000,
      total: 1100
    }


2. BACKEND - ORDER CREATION
═══════════════════════════════════════════════════════════════

                    Laravel API
                         ↓
            OrderController::store()
                         ↓
            ├─ Validate request
            ├─ Create Order record
            │  └─ Auto-generate order_ref: "ORD-20241211-001"
            ├─ Create OrderItem records
            ├─ event(new OrderCreated($order)) 📢
            │  ├─ OrderCreated broadcasts event
            │  ├─ Sends to 'orders' WebSocket channel
            │  └─ Admin receives notification
            └─ Return response
                {
                  success: true,
                  data: {id, order_ref, total, ...}
                }
                    ↓
            RESPONSE: 201 Created
            ←────────────────────


3. MOBILE APP - NOTIFICATION & POLLING
═══════════════════════════════════════════════════════════════

Response received
    ↓
NotificationService.notifyNewOrder()
    ├─ Emit 'newOrderCreated' event
    ├─ Save to local storage
    └─ Notify any listeners
    
NotificationService.startOrderStatusPolling(orderId)
    ├─ Store polling interval ID
    ├─ Poll every 15 seconds
    │
    └─→ GET /api/v1/orders/{id}
        ↓
        GET status from backend
        ↓
        OrderDetailsScreen.js
        ├─ Check if status changed
        ├─ Update UI with new status
        ├─ Save locally via cacheOrderUpdate()
        └─ Show "Order Confirmed" ✓


4. ADMIN DASHBOARD - RECEIVES NOTIFICATION
═══════════════════════════════════════════════════════════════

AdminOrderDashboard.js
├─ Listening to OrderCreated event
├─ Receives real-time notification 🔔
├─ Update state
│  ├─ setNewOrderCount(+1)
│  ├─ Add order to list
│  └─ Show notification banner
├─ Play notification sound
└─ Display in order list
   ├─ Order #ORD-20241211-001
   ├─ From: John Doe
   ├─ Amount: ₱1,100
   ├─ Status: Pending Confirmation
   └─ Buttons:
      ├─ ✓ Confirm Order
      ├─ ⚙ Mark as Processing
      └─ 👁 View Details


5. ADMIN - UPDATE ORDER STATUS
═══════════════════════════════════════════════════════════════

Admin clicks "Confirm Order"
    ↓
    REQUEST: PATCH /api/v1/admin/orders/1/status
    ─────────────────────────→
    
    {
      status: 'confirmed',
      notes: 'Order confirmed by admin'
    }
    
                Backend
                    ↓
        OrderController::updateStatus()
                    ↓
        ├─ Validate status
        ├─ Update Order::status = 'confirmed'
        ├─ Update Order::confirmed_at = now()
        ├─ event(new OrderStatusChanged($order)) 📢
        │  ├─ Broadcast status change
        │  ├─ Send to 'orders.1' channel
        │  └─ Mobile polling receives update
        └─ Return response
            {
              success: true,
              data: {id, status, confirmed_at, ...}
            }
                    ↓
        RESPONSE: 200 OK
        ←─────────────────────


6. MOBILE - RECEIVES STATUS UPDATE
═══════════════════════════════════════════════════════════════

Polling: GET /api/v1/orders/1
    ↓
Backend returns status: 'confirmed'
    ↓
NotificationService
├─ Detect status changed
├─ Emit 'orderStatusChanged' event
├─ Cache update locally
└─ Notify listeners
    ↓
OrderDetailsScreen.js
├─ Receives status update
├─ Update UI
├─ Show "Order Confirmed" ✓
├─ Refresh timeline
└─ Stop polling if delivered
```

## Database Schema

```
orders table
┌─────────────────────────────────────────────────┐
│ Column            │ Type      │ Notes           │
├─────────────────────────────────────────────────┤
│ id                │ BIGINT    │ PK              │
│ order_ref         │ VARCHAR   │ UNIQUE "ORD-..." │
│ user_id           │ BIGINT    │ FK (nullable)   │
│ customer_name     │ VARCHAR   │ Required        │
│ customer_phone    │ VARCHAR   │ Required        │
│ customer_email    │ VARCHAR   │ Nullable        │
│ subtotal          │ DECIMAL   │ Before fees     │
│ shipping_fee      │ DECIMAL   │ Delivery cost   │
│ discount          │ DECIMAL   │ Applied discount│
│ total             │ DECIMAL   │ Final amount    │
│ payment_method    │ ENUM      │ gcash/bank/cash │
│ payment_status    │ ENUM      │ pending/paid... │
│ payment_ref       │ VARCHAR   │ Transaction ID  │
│ status            │ ENUM      │ pending→shipped │
│ shipping_address  │ TEXT      │ Full address    │
│ confirmed_at      │ TIMESTAMP │ When confirmed  │
│ shipped_at        │ TIMESTAMP │ When shipped    │
│ delivered_at      │ TIMESTAMP │ When delivered  │
│ created_at        │ TIMESTAMP │ Order time      │
│ updated_at        │ TIMESTAMP │ Last change     │
└─────────────────────────────────────────────────┘

order_items table
┌─────────────────────────────────────┐
│ Column     │ Type    │ Notes        │
├─────────────────────────────────────┤
│ id         │ BIGINT  │ PK           │
│ order_id   │ BIGINT  │ FK           │
│ product_id │ BIGINT  │ FK           │
│ prod_name  │ VARCHAR │ Cache        │
│ quantity   │ INT     │ How many     │
│ price      │ DECIMAL │ Unit price   │
│ total      │ DECIMAL │ qty × price  │
└─────────────────────────────────────┘
```

## Real-Time Communication

```
Option 1: POLLING (Current Implementation)
═══════════════════════════════════════════════════════════════
Mobile → GET /api/v1/orders/1 (every 15 seconds)
         ↓
       Check status
         ↓
       If changed → Update UI

Pros: Works everywhere, simple
Cons: Higher server load, slight delay


Option 2: WEBSOCKETS (Future Enhancement)
═══════════════════════════════════════════════════════════════
Mobile ↔ WebSocket connection (persistent)
         ↓
       Receive updates instantly
         ↓
       Update UI immediately

Pros: True real-time, lower latency
Cons: More server resources


Option 3: SERVER-SENT EVENTS (Alternative)
═══════════════════════════════════════════════════════════════
Mobile ← Server → Stream of events
         ↓
       Receive updates as they happen
         ↓
       Update UI

Pros: One-way real-time, simpler than WebSockets
Cons: Can't send data to server
```

## Order Status Flow

```
                    ORDER LIFECYCLE
═══════════════════════════════════════════════════════════════

                pending_confirmation
                         ↑ Mobile places order
                         ↓
                    confirmed (admin confirms)
                         ↓
                    processing (admin processes)
                         ↓
                    shipped (admin ships)
                         ↓
                    delivered (customer receives) ✓

                  ALTERNATIVE PATHS:
                  
    At any stage → cancelled (order cancelled)
    After paid → refunded (refund processed)
```

## Security Architecture

```
AUTHENTICATION & AUTHORIZATION
═══════════════════════════════════════════════════════════════

Mobile App
├─ Send requests without auth (public endpoints)
└─ POST /api/v1/orders (any user)

Admin Web
├─ User logs in
├─ Receive Bearer token (Laravel Sanctum)
├─ Include token in headers
│  Authorization: Bearer {token}
└─ Access protected routes
   ├─ GET /api/v1/admin/orders (requires auth)
   └─ PATCH /api/v1/admin/orders/{id}/status (requires auth)

Database
├─ Transactions prevent data loss
├─ Indexes speed up queries
├─ Foreign keys maintain integrity
└─ Timestamps track changes
```

## Performance Optimization

```
INDEXING STRATEGY
═══════════════════════════════════════════════════════════════

Indexes on:
├─ id (primary key)
├─ order_ref (unique)
├─ user_id (foreign key)
├─ status (for filtering)
├─ payment_status (for filtering)
├─ created_at (for sorting)
└─ (created_at, status) composite (most queries)

Query patterns:
├─ Get orders by status: instant ⚡
├─ Get recent orders: instant ⚡
├─ Search by order_ref: instant ⚡
└─ Pagination: efficient ⚡
```

---

**Architecture Version:** 1.0  
**Last Updated:** December 11, 2024  
**Status:** Production Ready ✅
