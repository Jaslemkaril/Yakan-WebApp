# 📋 Copy Files Setup Guide

## 🎯 Goal
Copy order notification system files from YAKAN-main-main to YAKAN-WEB-main (Laravel backend)

## 📂 Files to Copy

You need to copy **6 files total** from the mobile app folder to the Laravel backend folder.

---

## 🔧 Step-by-Step Instructions

### Step 1: Open Two File Explorer Windows

**Window 1 - Source (Mobile App):**
```
C:\xampp\htdocs\YAKAN-main-main
```

**Window 2 - Destination (Laravel Backend):**
```
C:\xampp\htdocs\YAKAN-WEB-main
```

Arrange them side by side so you can see both.

---

### Step 2: Copy Model Files

#### File 1: Order.php

**FROM:** `C:\xampp\htdocs\YAKAN-main-main\app\Models\Order.php`

**TO:** `C:\xampp\htdocs\YAKAN-WEB-main\app\Models\Order.php`

Steps:
1. In Window 1, navigate to `YAKAN-main-main\app\Models\`
2. Right-click `Order.php`
3. Select "Copy"
4. In Window 2, navigate to `YAKAN-WEB-main\app\Models\`
5. Right-click → "Paste"
6. Click "Yes" if asked to overwrite

#### File 2: OrderItem.php

**FROM:** `C:\xampp\htdocs\YAKAN-main-main\app\Models\OrderItem.php`

**TO:** `C:\xampp\htdocs\YAKAN-WEB-main\app\Models\OrderItem.php`

Repeat the copy-paste steps above.

---

### Step 3: Copy Controller File

#### File 3: OrderController.php

**FROM:** `C:\xampp\htdocs\YAKAN-main-main\app\Http\Controllers\OrderController.php`

**TO:** `C:\xampp\htdocs\YAKAN-WEB-main\app\Http\Controllers\OrderController.php`

Steps:
1. In Window 1, navigate to `YAKAN-main-main\app\Http\Controllers\`
2. Right-click `OrderController.php`
3. Select "Copy"
4. In Window 2, navigate to `YAKAN-WEB-main\app\Http\Controllers\`
5. Right-click → "Paste"

---

### Step 4: Copy Event Files

#### File 4: OrderCreated.php

**FROM:** `C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderCreated.php`

**TO:** `C:\xampp\htdocs\YAKAN-WEB-main\app\Events\OrderCreated.php`

Steps:
1. In Window 1, navigate to `YAKAN-main-main\app\Events\`
2. Right-click `OrderCreated.php`
3. Select "Copy"
4. In Window 2, navigate to `YAKAN-WEB-main\app\Events\` (create folder if needed)
5. Right-click → "Paste"

#### File 5: OrderStatusChanged.php

**FROM:** `C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderStatusChanged.php`

**TO:** `C:\xampp\htdocs\YAKAN-WEB-main\app\Events\OrderStatusChanged.php`

Repeat the copy-paste steps above.

---

### Step 5: Copy Migration File

#### File 6: Database Migration

**FROM:** `C:\xampp\htdocs\YAKAN-main-main\database\migrations\2024_12_11_create_orders_table.php`

**TO:** `C:\xampp\htdocs\YAKAN-WEB-main\database\migrations\2024_12_11_create_orders_table.php`

Steps:
1. In Window 1, navigate to `YAKAN-main-main\database\migrations\`
2. Right-click `2024_12_11_create_orders_table.php`
3. Select "Copy"
4. In Window 2, navigate to `YAKAN-WEB-main\database\migrations\`
5. Right-click → "Paste"

---

## ✅ Verification Checklist

After copying, verify all files are in place:

### In YAKAN-WEB-main:

- [ ] `app/Models/Order.php` exists
- [ ] `app/Models/OrderItem.php` exists
- [ ] `app/Http/Controllers/OrderController.php` exists
- [ ] `app/Events/OrderCreated.php` exists
- [ ] `app/Events/OrderStatusChanged.php` exists
- [ ] `database/migrations/2024_12_11_create_orders_table.php` exists

---

## 🔄 Alternative: Use PowerShell

If you prefer command line, open PowerShell and run:

```powershell
# Navigate to YAKAN-WEB-main
cd C:\xampp\htdocs\YAKAN-WEB-main

# Copy Models
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Models\Order.php" -Destination "app\Models\" -Force
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Models\OrderItem.php" -Destination "app\Models\" -Force

# Copy Controller
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Http\Controllers\OrderController.php" -Destination "app\Http\Controllers\" -Force

# Create Events folder if needed
if (!(Test-Path "app\Events")) { New-Item -ItemType Directory -Path "app\Events" }

# Copy Events
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderCreated.php" -Destination "app\Events\" -Force
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\app\Events\OrderStatusChanged.php" -Destination "app\Events\" -Force

# Copy Migration
Copy-Item "C:\xampp\htdocs\YAKAN-main-main\database\migrations\2024_12_11_create_orders_table.php" -Destination "database\migrations\" -Force

Write-Host "✅ All files copied successfully!" -ForegroundColor Green
```

---

## 🗂️ File Structure Reference

After copying, your YAKAN-WEB-main folder should look like this:

```
YAKAN-WEB-main/
├── app/
│   ├── Models/
│   │   ├── Order.php ✨ NEW
│   │   ├── OrderItem.php ✨ NEW
│   │   ├── User.php
│   │   └── Product.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── OrderController.php ✨ NEW
│   │       ├── ProductController.php
│   │       └── ...
│   └── Events/
│       ├── OrderCreated.php ✨ NEW
│       ├── OrderStatusChanged.php ✨ NEW
│       └── ...
│
├── database/
│   └── migrations/
│       ├── 2024_12_11_create_orders_table.php ✨ NEW
│       ├── ...
│       └── ...
│
└── ... (other folders)
```

---

## ⚡ Next Steps After Copying

After all files are copied, follow these steps:

### 1. Run Database Migration
```bash
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan migrate
```

### 2. Update Routes (routes/api.php)

Add this code to `routes/api.php`:

```php
<?php

use App\Http\Controllers\OrderController;

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

### 3. Test the API

```bash
# Test if orders can be created
curl -X POST http://127.0.0.1:8000/api/v1/orders \
  -H "Content-Type: application/json" \
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

---

## 🚨 Common Issues

### Issue: "File already exists"
**Solution:** Click "Yes" to overwrite (new versions are better)

### Issue: "Folder doesn't exist"
**Solution:** Create the folder manually:
- Right-click in the parent folder
- Select "New" → "Folder"
- Name it (e.g., "Events")

### Issue: Migration fails
**Solution:** Make sure all 6 files are copied correctly
```bash
# Check if files exist
Get-ChildItem "C:\xampp\htdocs\YAKAN-WEB-main\app\Models\Order.php"
Get-ChildItem "C:\xampp\htdocs\YAKAN-WEB-main\app\Models\OrderItem.php"
Get-ChildItem "C:\xampp\htdocs\YAKAN-WEB-main\app\Http\Controllers\OrderController.php"
Get-ChildItem "C:\xampp\htdocs\YAKAN-WEB-main\app\Events\OrderCreated.php"
Get-ChildItem "C:\xampp\htdocs\YAKAN-WEB-main\app\Events\OrderStatusChanged.php"
Get-ChildItem "C:\xampp\htdocs\YAKAN-WEB-main\database\migrations\2024_12_11_create_orders_table.php"
```

---

## ✅ Quick Checklist

- [ ] Copy Order.php to app/Models/
- [ ] Copy OrderItem.php to app/Models/
- [ ] Copy OrderController.php to app/Http/Controllers/
- [ ] Copy OrderCreated.php to app/Events/
- [ ] Copy OrderStatusChanged.php to app/Events/
- [ ] Copy migration file to database/migrations/
- [ ] Run `php artisan migrate`
- [ ] Update routes/api.php
- [ ] Test API with curl

---

## 🎯 Summary

**6 files to copy:**
1. `app/Models/Order.php`
2. `app/Models/OrderItem.php`
3. `app/Http/Controllers/OrderController.php`
4. `app/Events/OrderCreated.php`
5. `app/Events/OrderStatusChanged.php`
6. `database/migrations/2024_12_11_create_orders_table.php`

**From:** `C:\xampp\htdocs\YAKAN-main-main\`
**To:** `C:\xampp\htdocs\YAKAN-WEB-main\`

**Time needed:** ~10 minutes (file explorer) or ~2 minutes (PowerShell)

**Mobile app:** Already has all files updated ✅

---

**Need help?** See IMPLEMENTATION_CHECKLIST.md for more details.
