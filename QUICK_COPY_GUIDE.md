# 📋 File Copy Quick Reference

## 🎯 What to Copy & Where

### Source: `C:\xampp\htdocs\YAKAN-main-main`
### Destination: `C:\xampp\htdocs\YAKAN-WEB-main`

---

## 📂 File-by-File Copy Instructions

```
COPY FROM                                    TO
═══════════════════════════════════════════════════════════════════════════

1. app/Models/Order.php
   C:\xampp\htdocs\YAKAN-main-main\app\Models\Order.php
                                ↓
                     C:\xampp\htdocs\YAKAN-WEB-main\app\Models\Order.php

2. app/Models/OrderItem.php
   C:\xampp\htdocs\YAKAN-main-main\app\Models\OrderItem.php
                                ↓
                     C:\xampp\htdocs\YAKAN-WEB-main\app\Models\OrderItem.php

3. app/Http/Controllers/OrderController.php
   C:\xampp\htdocs\YAKAN-main-main\app\Http\Controllers\OrderController.php
                                ↓
                     C:\xampp\htdocs\YAKAN-WEB-main\app\Http\Controllers\OrderController.php

4. app/Events/OrderCreated.php
   C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderCreated.php
                                ↓
                     C:\xampp\htdocs\YAKAN-WEB-main\app\Events\OrderCreated.php

5. app/Events/OrderStatusChanged.php
   C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderStatusChanged.php
                                ↓
                     C:\xampp\htdocs\YAKAN-WEB-main\app\Events\OrderStatusChanged.php

6. database/migrations/2024_12_11_create_orders_table.php
   C:\xampp\htdocs\YAKAN-main-main\database\migrations\2024_12_11_create_orders_table.php
                                ↓
                     C:\xampp\htdocs\YAKAN-WEB-main\database\migrations\2024_12_11_create_orders_table.php
```

---

## 🖱️ Easiest Method: Windows File Explorer

### Quick Steps:

1. **Open File Explorer** (Win+E)

2. **In Address Bar 1, paste:** `C:\xampp\htdocs\YAKAN-main-main`
   - Press Enter

3. **In Address Bar 2, paste:** `C:\xampp\htdocs\YAKAN-WEB-main`
   - Press Enter in a new window

4. **For each of 6 files:**
   - Find it in left window
   - Right-click → Copy
   - Navigate to same folder in right window
   - Right-click → Paste
   - Say Yes if asked to overwrite

---

## ⚡ Fastest Method: PowerShell

### Copy-Paste This (all 6 files at once):

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main

Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Models\Order.php" -Destination "app\Models\" -Force
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Models\OrderItem.php" -Destination "app\Models\" -Force
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Http\Controllers\OrderController.php" -Destination "app\Http\Controllers\" -Force
if (!(Test-Path "app\Events")) { New-Item -ItemType Directory -Path "app\Events" }
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderCreated.php" -Destination "app\Events\" -Force
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderStatusChanged.php" -Destination "app\Events\" -Force
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\database\migrations\2024_12_11_create_orders_table.php" -Destination "database\migrations\" -Force

Write-Host "✅ All files copied successfully!" -ForegroundColor Green
```

---

## ✅ After Copying

### 1. Run Database Migration
```bash
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan migrate
```

Expected output:
```
Migration table created successfully.
Migrating: 2024_12_11_create_orders_table
Migrated: 2024_12_11_create_orders_table
```

### 2. Update routes/api.php

Add these routes (copy-paste at the end):

```php
<?php
use App\Http\Controllers\OrderController;

Route::middleware('api')->prefix('v1')->group(function () {
    
    // Mobile
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    // Admin
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
        Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});
```

### 3. Verify It Works

```bash
# Test creating an order
curl -X POST http://127.0.0.1:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Test",
    "customer_phone": "09171234567",
    "shipping_address": "123 Test St",
    "payment_method": "gcash",
    "subtotal": 1000,
    "total": 1100,
    "shipping_fee": 100,
    "items": [{"product_id": 1, "quantity": 1, "price": 1000}]
  }'
```

You should get a response with `"success": true`

---

## 🎯 Summary

| Step | Action | Time |
|------|--------|------|
| 1 | Copy 6 files to YAKAN-WEB-main | 5 min |
| 2 | Run `php artisan migrate` | 1 min |
| 3 | Update routes/api.php | 2 min |
| 4 | Test API | 2 min |
| **TOTAL** | **Done!** | **10 min** |

---

## 🚀 You're Done!

Mobile app can now:
- ✅ Place orders
- ✅ Get status updates
- ✅ See admin notifications

Admin can:
- ✅ View orders
- ✅ Update status
- ✅ Manage everything

---

**See COPY_FILES_GUIDE.md for detailed step-by-step with more info.**
