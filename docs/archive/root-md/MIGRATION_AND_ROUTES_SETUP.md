# 🗄️ Database Migration & Routes Setup

## Step 1: Run Database Migration

Open PowerShell in YAKAN-WEB-main folder and run:

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan migrate
```

**Expected Output:**
```
Migration table created successfully.
Migrating: 2024_12_11_create_orders_table
Migrated:  2024_12_11_create_orders_table
```

✅ If you see "Migrated: 2024_12_11_create_orders_table" → **Success!**

---

## Step 2: Update Routes

Open this file:
```
C:\xampp\htdocs\YAKAN-WEB-main\routes\api.php
```

Find the existing routes code and **ADD this code** at the end (before the closing `});`):

```php
// ===================== ORDER ROUTES =====================
Route::middleware('api')->prefix('v1')->group(function () {
    
    // ===================== MOBILE - ORDER SUBMISSION =====================
    Route::post('/orders', [OrderController::class, 'store'])
        ->name('orders.store');

    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{id}', [OrderController::class, 'show'])
        ->name('orders.show');

    
    // ===================== ADMIN - ORDER MANAGEMENT =====================
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'adminIndex'])
            ->name('admin.orders.index');

        Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus'])
            ->name('admin.orders.update-status');
    });
});
```

**Don't forget to add the import at the top:**
```php
use App\Http\Controllers\OrderController;
```

---

## Step 3: Test the API

Run Laravel server:
```powershell
php artisan serve
```

In another terminal, test if orders endpoint works:
```powershell
curl -X POST http://127.0.0.1:8000/api/v1/orders `
  -H "Content-Type: application/json" `
  -d '{
    "customer_name": "Test User",
    "customer_phone": "09171234567",
    "shipping_address": "123 Test Street",
    "payment_method": "gcash",
    "subtotal": 1000,
    "total": 1100,
    "shipping_fee": 100,
    "items": [{"product_id": 1, "quantity": 1, "price": 1000}]
  }'
```

**Expected:** Should return the created order with an ID

---

## ✅ Verification Checklist

- [ ] Migration ran successfully (no errors)
- [ ] Routes file updated with order routes
- [ ] OrderController imported at top of routes/api.php
- [ ] Laravel server running (`php artisan serve`)
- [ ] API test returned order data

---

## 🎯 You're Done!

If all checks pass, your backend is **ready to receive orders from the mobile app**! 🚀

**Next:** 
- Mobile app already configured to use ngrok tunnel
- Tomorrow at school: Update ngrok URL (see NGROK_SETUP_FOR_DEFENSE.md)
- Run the demo and impress your teachers! 🎉
