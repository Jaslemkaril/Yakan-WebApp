# 🔧 API Error: 500 Internal Server Error Fix

## Problem
When placing an order, getting: "The total amount field is required" error or 500 error

## Root Causes

1. **Routes not added to `routes/api.php`** (Most Likely!)
2. **OrderController file not in correct location**
3. **Model files missing**
4. **Migration not run**
5. **Database tables don't exist**

---

## Fix Checklist

### Step 1: Verify Files Copied Successfully

In PowerShell, check if all files exist in YAKAN-WEB-main:

```powershell
# Navigate to YAKAN-WEB-main
cd C:\xampp\htdocs\YAKAN-WEB-main

# Check if all files exist
Test-Path "app\Models\Order.php"
Test-Path "app\Models\OrderItem.php"
Test-Path "app\Http\Controllers\OrderController.php"
Test-Path "app\Events\OrderCreated.php"
Test-Path "app\Events\OrderStatusChanged.php"
Test-Path "database\migrations\*create_orders_table*"
```

All should return `True`. If not, copy them again!

---

### Step 2: Verify Routes Added

Open **`C:\xampp\htdocs\YAKAN-WEB-main\routes\api.php`**

**Check if this code is there:**

```php
// Add this import at the top
use App\Http\Controllers\OrderController;

// Add these routes (should be inside a Route::group or at the end)
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
```

If NOT there, **add it now!**

---

### Step 3: Run Migration

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan migrate
```

You should see:
```
Migrated:  2024_12_11_create_orders_table
```

---

### Step 4: Clear Cache

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan cache:clear
php artisan config:clear
php artisan route:cache
```

---

### Step 5: Restart Laravel

Stop the Laravel server (Ctrl+C in the terminal) and restart:

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve
```

---

## Test Again

Try placing an order again in the mobile app.

If still failing, check the Laravel log for the exact error:

```powershell
Get-Content "C:\xampp\htdocs\YAKAN-WEB-main\storage\logs\laravel.log" -Tail 20
```

---

## If Still Not Working

Tell me the exact error message from the log, and I can fix it specifically!

Common errors:
- "Call to undefined method" → File not copied correctly
- "Table 'yakan.orders' doesn't exist" → Migration not run
- "Route not found" → Routes not added
- "Class not found" → Namespace issue

Let me know which one you get!
