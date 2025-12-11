# ✅ Test: Order Flow Mobile → Admin

## Test Steps

### Step 1: Open Mobile App
- Go to http://localhost:8082 (or scan QR code)
- Browse products
- Add item to cart
- Go to checkout

### Step 2: Place Order
- Fill shipping info
- Select payment method (GCash or Bank Transfer)
- Click "Confirm Payment"
- You should see: "Order placed successfully!" ✅

**Expected in Console:**
```
🔵 Sending order to API: {...}
🔵 Order created successfully: {...}
🔔 Triggering admin notification for new order: ...
```

### Step 3: Verify Order in Database
Open PowerShell and check if order was saved:

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main

# Connect to database and check orders
php artisan tinker
```

Then in tinker:
```php
>>> DB::table('orders')->latest()->first();
```

You should see your order! If yes ✅, then the API is working.

### Step 4: Check Admin API Endpoint
Test if admin can retrieve orders:

```powershell
$response = Invoke-WebRequest -Uri "https://preeternal-ungraded-jere.ngrok-free.dev/api/v1/admin/orders" `
  -UseBasicParsing -Headers @{'ngrok-skip-browser-warning'='true'} -ErrorAction SilentlyContinue

$response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 3
```

You should see your order in the response ✅

### Step 5: Import Admin Dashboard to Web
Now we need to put the admin dashboard in the web app to display these orders.

The AdminOrderDashboard component is in:
```
C:\xampp\htdocs\YAKAN-main-main\src\components\AdminOrderDashboard.js
```

You need to import and use it in your YAKAN-WEB-main Laravel app.

---

## What Should Happen

1. ✅ Order placed in mobile app
2. ✅ Order saved to database via API
3. ✅ Admin can query orders via `/api/v1/admin/orders`
4. ✅ Admin dashboard component displays orders in real-time
5. ✅ Admin can update order status
6. ✅ Mobile app sees status updates (via polling)

---

## Quick Check Checklist

- [ ] Mobile app can place order without 500 error
- [ ] Order appears in database (`php artisan tinker`)
- [ ] Admin API endpoint returns orders (`/api/v1/admin/orders`)
- [ ] OrderController.php is in YAKAN-WEB-main
- [ ] Routes include admin order endpoints

---

## If Order Doesn't Appear

1. Check Laravel log for errors
2. Verify `OrderController.php` was copied correctly
3. Verify routes were added to `routes/api.php`
4. Check database connection is working

Run:
```powershell
php artisan db:show
```

Should show database info.
