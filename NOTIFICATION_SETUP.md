# Real-Time Order Notification System

This document explains how to set up real-time notifications for order submission from mobile to admin dashboard.

## Architecture Overview

```
Mobile App (React Native/Expo)
    ↓
    └─→ Submits Order (POST /api/v1/orders)
        ↓
        └─→ Laravel Backend
            ├─→ Validates order data
            ├─→ Creates Order in DB
            ├─→ Creates OrderItems in DB
            ├─→ Broadcasts OrderCreated Event 📢
            └─→ Returns success response
                ↓
                └─→ Mobile App
                    ├─→ Stores order locally
                    ├─→ Starts polling for status updates
                    └─→ Shows confirmation screen
                    
Admin Dashboard (Web)
    ↓
    └─→ Listening for notifications
        ├─→ Receives OrderCreated event in real-time
        ├─→ Displays notification banner 🔔
        ├─→ Updates order list with new order
        └─→ Shows confirmation/processing buttons
            ↓
            └─→ Admin clicks "Confirm Order"
                ├─→ Sends PATCH /api/v1/admin/orders/{id}/status
                ├─→ Backend updates order status
                ├─→ Broadcasts OrderStatusChanged event
                └─→ Mobile app receives status update
                    └─→ Updates order status in real-time
```

## File Structure

```
YAKAN-main-main/
├── NOTIFICATION_SETUP.md                    ← This file
├── src/
│   ├── services/
│   │   ├── notificationService.js           ← Real-time notification management
│   │   ├── api.js                           ← API calls (updated)
│   │   └── orderService.js                  ← Order operations
│   ├── screens/
│   │   ├── PaymentScreen.js                 ← Updated with notifications
│   │   ├── CheckoutScreen.js
│   │   └── OrderDetailsScreen.js
│   ├── components/
│   │   └── AdminOrderDashboard.js           ← Admin UI component
│   └── config/
│       └── config.js                        ← API endpoints
│
└── Laravel Backend/
    ├── app/
    │   ├── Models/
    │   │   ├── Order.php                    ← Order model
    │   │   └── OrderItem.php                ← Order items model
    │   ├── Http/Controllers/
    │   │   └── OrderController.php          ← Order API endpoints
    │   ├── Events/
    │   │   ├── OrderCreated.php             ← Broadcast event
    │   │   └── OrderStatusChanged.php       ← Broadcast event
    │   └── Listeners/
    │       └── SendOrderNotification.php    ← Handle notifications
    │
    ├── database/
    │   └── migrations/
    │       └── 2024_12_11_create_orders_table.php
    │
    └── routes/
        └── api.php                          ← Add order routes
```

## Setup Instructions

### Step 1: Create Database Tables

Run the migration to create the orders table:

```bash
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan migrate
```

**What this creates:**
- `orders` table - stores order data
- `order_items` table - stores individual items per order
- Indexes on status, payment_status, and dates for faster queries

### Step 2: Add Order Models

Copy these files to your Laravel project:

```
app/Models/Order.php
app/Models/OrderItem.php
```

**What these do:**
- Define order structure and relationships
- Provide helper methods (status_label, scopes)
- Auto-generate unique order reference numbers

### Step 3: Create Order Controller

Copy this file to your Laravel project:

```
app/Http/Controllers/OrderController.php
```

**What this provides:**
- `POST /api/v1/orders` - Mobile submits order
- `GET /api/v1/orders` - User views their orders
- `GET /api/v1/admin/orders` - Admin views all orders
- `PATCH /api/v1/admin/orders/{id}/status` - Admin updates status

### Step 4: Create Events for Broadcasting

Copy these files:

```
app/Events/OrderCreated.php
app/Events/OrderStatusChanged.php
```

**What these do:**
- Broadcast real-time notifications to connected clients
- Send data to WebSocket or Server-Sent Events listeners
- Trigger admin notifications when orders arrive

### Step 5: Update API Routes

Add to `routes/api.php`:

```php
<?php

use App\Http\Controllers\OrderController;

Route::middleware('api')->prefix('v1')->group(function () {
    
    // Mobile - Order submission
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    // Admin - Order management
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
        Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});
```

### Step 6: Update Mobile App

The following files are already updated:

**Services:**
- `src/services/notificationService.js` - NEW - Handles notifications
- `src/services/api.js` - Updated with order endpoints

**Screens:**
- `src/screens/PaymentScreen.js` - Updated to submit orders and notify admin

**Configuration:**
- `src/config/config.js` - Already has API endpoints

### Step 7: Create Admin Dashboard

Copy the component:

```
src/components/AdminOrderDashboard.js
```

Then use it in your web admin page:

```jsx
import AdminOrderDashboard from './components/AdminOrderDashboard';

function AdminPage() {
  return <AdminOrderDashboard />;
}
```

## API Endpoints

### Submit Order (Mobile)

```
POST /api/v1/orders
Headers: 
  Content-Type: application/json
  Authorization: Bearer {token} (optional)

Request Body:
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "09171234567",
  "shipping_address": "123 Main St",
  "shipping_city": "Manila",
  "shipping_province": "Metro Manila",
  "payment_method": "gcash",
  "payment_status": "paid",
  "payment_reference": "GC123456789",
  "subtotal": 1000,
  "shipping_fee": 100,
  "discount": 0,
  "total": 1100,
  "delivery_type": "deliver",
  "notes": "Please deliver in the morning",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 500
    }
  ]
}

Response (201):
{
  "success": true,
  "message": "Order created successfully. Admin will be notified.",
  "data": {
    "id": 1,
    "orderRef": "ORD-20241211-001",
    "customerName": "John Doe",
    "total": 1100,
    "status": "pending_confirmation",
    "items": [...]
  }
}
```

### Get Admin Orders

```
GET /api/v1/admin/orders?status=pending_confirmation&limit=20
Headers:
  Authorization: Bearer {admin_token}

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "orderRef": "ORD-20241211-001",
      "customerName": "John Doe",
      "customerPhone": "09171234567",
      "status": "pending_confirmation",
      "total": 1100,
      "items": [...]
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 20,
    "current_page": 1
  }
}
```

### Update Order Status (Admin)

```
PATCH /api/v1/admin/orders/{id}/status
Headers:
  Authorization: Bearer {admin_token}
  Content-Type: application/json

Request Body:
{
  "status": "confirmed",
  "notes": "Order confirmed by admin"
}

Response:
{
  "success": true,
  "message": "Order status updated successfully",
  "data": {
    "id": 1,
    "status": "confirmed",
    "confirmedAt": "2024-12-11T10:30:00Z"
  }
}
```

## Real-Time Notification Flow

### 1. Mobile Submits Order

```javascript
// In PaymentScreen.js - handleConfirmPayment()

const response = await ApiService.createOrder(apiOrderData);

// Notify admin
NotificationService.notifyNewOrder({
  orderId: response.data?.id,
  orderRef: finalOrderData.orderRef,
  customerName: orderData.shippingAddress.fullName,
  total: orderData.total,
  status: 'pending_confirmation',
});

// Start polling for status updates
NotificationService.startOrderStatusPolling(
  response.data.id,
  (updatedOrder) => {
    // Order status changed, update UI
    console.log('Order status:', updatedOrder.status);
  }
);
```

### 2. Admin Dashboard Receives Notification

```javascript
// In AdminOrderDashboard.js

useEffect(() => {
  const unsubscribe = subscribeToOrderNotifications();
  return unsubscribe;
}, []);

const subscribeToOrderNotifications = () => {
  const handleNewOrder = (orderData) => {
    // Show notification banner
    setNewOrderCount(prev => prev + 1);
    setNotificationMessage(`New order #${orderData.order.orderRef}`);
    
    // Add to list
    setOrders(prev => [orderData.order, ...prev]);
    
    // Play sound
    playNotificationSound();
  };

  // Connect to WebSocket or polling
  // return unsubscribe function
};
```

### 3. Admin Updates Order Status

```javascript
// In AdminOrderDashboard.js

const handleConfirmOrder = async (orderId) => {
  const response = await fetch(`/api/v1/admin/orders/${orderId}/status`, {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      status: 'confirmed',
      notes: 'Order confirmed by admin',
    }),
  });

  // Update local state
  setOrders(prev => prev.map(order => 
    order.id === orderId ? { ...order, status: 'confirmed' } : order
  ));
};
```

### 4. Mobile Receives Status Update

```javascript
// In notificationService.js - startOrderStatusPolling()

setInterval(async () => {
  const order = await ApiService.getOrder(orderId);
  
  // Emit notification
  this.emit('orderStatusChanged', {
    orderId,
    order: order.data,
  });
  
  // Cache locally
  await this.cacheOrderUpdate(orderId, order.data);
}, pollingInterval);
```

## Real-Time Communication Options

### Option 1: Polling (Current Implementation)
- **Interval:** 15 seconds
- **Pros:** Works everywhere, no setup needed
- **Cons:** Higher server load, slight delay
- **Best for:** MVP, testing, slow networks

### Option 2: WebSockets
- **Setup:** Use Laravel WebSockets package
- **Pros:** True real-time, lower latency
- **Cons:** More server resources
- **Best for:** Production, high-traffic apps

### Option 3: Server-Sent Events (SSE)
- **Setup:** Use Laravel streams
- **Pros:** Simpler than WebSockets
- **Cons:** One-way communication
- **Best for:** Notifications only

## Status Workflow

```
Order Created (pending_confirmation)
    ↓
Admin Confirms → Order Confirmed
    ↓
Admin Processes → Order Processing
    ↓
Admin Ships → Order Shipped
    ↓
Delivery Complete → Order Delivered
```

Alternative paths:
- Cancel at any stage → Order Cancelled
- Refund after payment verified → Order Refunded

## Testing the System

### 1. Test Order Submission

```bash
# Using curl to test order API
curl -X POST https://preeternal-ungraded-jere.ngrok-free.dev/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Test Customer",
    "customer_phone": "09171234567",
    "shipping_address": "123 Test St",
    "payment_method": "gcash",
    "subtotal": 1000,
    "total": 1100,
    "shipping_fee": 100,
    "items": [{"product_id": 1, "quantity": 1, "price": 1000}]
  }'
```

### 2. Test Admin Retrieval

```bash
curl -X GET "https://preeternal-ungraded-jere.ngrok-free.dev/api/v1/admin/orders?status=pending_confirmation" \
  -H "Authorization: Bearer {admin_token}"
```

### 3. Test Status Update

```bash
curl -X PATCH https://preeternal-ungraded-jere.ngrok-free.dev/api/v1/admin/orders/1/status \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "confirmed"}'
```

## Troubleshooting

### Issue: Orders not appearing in admin dashboard
**Solution:**
1. Check if OrderController is properly registered in routes
2. Verify notification service is running
3. Check browser console for JavaScript errors
4. Verify admin has authentication token

### Issue: Mobile app not receiving status updates
**Solution:**
1. Check polling interval in notificationService.js
2. Verify order ID is being passed correctly
3. Check network connectivity
4. Verify backend is returning correct status

### Issue: Duplicate orders in database
**Solution:**
1. Add unique constraint on payment_reference + customer_phone
2. Implement idempotency key on API
3. Add database transaction locking

## Database Schema

### Orders Table

| Column | Type | Notes |
|--------|------|-------|
| id | BigInt | Primary key |
| order_ref | String | Unique order number (ORD-20241211-001) |
| user_id | BigInt | User who placed order (nullable) |
| customer_name | String | Customer name from mobile |
| customer_email | String | Customer email |
| customer_phone | String | Contact number |
| subtotal | Decimal | Before shipping & discount |
| shipping_fee | Decimal | Delivery cost |
| discount | Decimal | Applied discount |
| total | Decimal | Final amount |
| payment_method | Enum | gcash, bank_transfer, cash |
| payment_status | Enum | pending, paid, verified, failed |
| payment_reference | String | Transaction ID from payment |
| status | Enum | pending_confirmation, confirmed, processing, shipped, delivered, cancelled |
| shipping_address | Text | Full address |
| notes | Text | Customer notes |
| admin_notes | Text | Admin notes |
| source | String | mobile or web |
| created_at | Timestamp | Order creation time |
| updated_at | Timestamp | Last update time |

## Next Steps

1. ✅ Create models and migrations
2. ✅ Create API endpoints
3. ✅ Add real-time events
4. 🔄 Implement WebSocket for production
5. 🔄 Add order tracking page for customers
6. 🔄 Add SMS/Email notifications
7. 🔄 Generate order receipts/invoices

## Support

For questions or issues:
1. Check browser console for errors
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Test API endpoints directly with curl
4. Verify network connectivity with ngrok

