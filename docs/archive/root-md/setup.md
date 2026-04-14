# Yakan E-Commerce - XAMPP Local Setup Guide

## Overview
This guide documents the complete setup process for running the Yakan E-Commerce Laravel project locally using XAMPP, including resolving database driver mismatches (SQLite → MySQL).

## Prerequisites Installed
- ✅ XAMPP with PHP 8.2.12 (Located at `C:\Users\HP\Desktop\xampp`)
- ✅ Composer (Global installation)
- ✅ Node.js and npm

## Issues Resolved During Setup

### 1. PHP Version Mismatch
**Problem:** Initial XAMPP installation had PHP 8.0.30, but Laravel 11 requires PHP 8.2+

**Solution:** Updated to XAMPP with PHP 8.2.12 installed at `C:\Users\HP\Desktop\xampp`

**Verification:**
```bash
C:\Users\HP\Desktop\xampp\php\php.exe -v
# Output: PHP 8.2.12
```

### 2. Database Driver Mismatch (SQLite → MySQL)
**Problem:** Project was initially configured for SQLite, but XAMPP uses MySQL/MariaDB

**Solution:** Updated `.env` configuration to use MySQL

**Changes Made:**
```env
# Before (SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# After (MySQL with XAMPP)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yakan_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Missing Yakan Patterns Data
**Problem:** Custom order patterns page was empty after migration

**Solution:** Ran the YakanPatternSeeder to populate 14 traditional patterns

**Command:**
```bash
php artisan db:seed --class=YakanPatternSeeder
```

**Patterns Added:**
- Sinaluan (Sacred wedding pattern)
- Bunga Sama (Floral pattern)
- Pinalantikan (Nested diamond)
- Suhul (Ocean wave)
- Kabkaban (Interlocking squares)
- Laggi (Eight-pointed star)
- Bennig (Sacred spiral)
- Pangapun (Triangle/mountain)
- Sarang Kayu (Honeycomb)
- Ikan Mas (Fish pattern)
- Kalasag (Shield pattern)
- Tali (Rope/knot)
- Langgal (Mosque pattern)
- Saput Tangan (Handkerchief)

## Complete Setup Process

### Step 1: Environment Configuration
Created `.env` file with XAMPP-compatible settings:

```env
APP_NAME=Yakan
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yakan_db
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### Step 2: Start XAMPP Services
1. Opened XAMPP Control Panel: `C:\Users\HP\Desktop\xampp\xampp-control.exe`
2. Started **Apache** module
3. Started **MySQL** module

### Step 3: Create Database
1. Accessed phpMyAdmin: http://localhost/phpmyadmin
2. Created new database: `yakan_db`
3. Used default collation: `utf8mb4_general_ci`

### Step 4: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 5: Generate Application Key
```bash
php artisan key:generate
```

### Step 6: Run Database Migrations
```bash
php artisan migrate
```

**Migrations Created:**
- users table
- password_reset_tokens table
- sessions table
- cache tables
- jobs/failed_jobs tables
- products table
- categories table
- orders table
- order_items table
- custom_orders table
- yakan_patterns table
- inventories table
- payments table
- And more...

### Step 7: Seed Database
```bash
# Run all seeders
php artisan db:seed

# Specifically seed Yakan patterns
php artisan db:seed --class=YakanPatternSeeder
```

**Seeded Data:**
- Admin users (AdminUserSeederUpdated)
- Sample products and categories
- Inventory items
- 14 Traditional Yakan patterns

### Step 8: Link Storage
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public` for file uploads.

### Step 9: Build Frontend Assets
```bash
# Development build with hot reload
npm run dev

# Or production build
npm run build
```

### Step 10: Start Development Server
```bash
php artisan serve
```

Server started at: **http://127.0.0.1:8000**

## Login Credentials

### Admin Accounts
- **Email:** admin@yakan.com  
  **Password:** admin123

- **Email:** kariljaslem@gmail.com  
  **Password:** admin123

### User Account
- **Email:** user@yakan.com  
  **Password:** user123

## Key Routes Verified

```bash
php artisan route:list
```

Important routes available:
- `/` - Homepage
- `/login` - User login
- `/register` - User registration
- `/dashboard` - User dashboard
- `/products` - Product listing
- `/custom-orders/create/pattern` - Pattern selection (now populated)
- `/admin/*` - Admin panel routes

## File Structure Changes

### Created Files:
- `.env` - Environment configuration
- `public/storage` - Symbolic link to storage

### Modified Files:
- `composer.lock` - After dependency installation
- `package-lock.json` - After npm installation

## Database Schema

### Key Tables:
- **users** - User authentication and profiles
- **yakan_patterns** - Traditional weaving patterns (14 records)
- **products** - Product catalog
- **custom_orders** - Custom weaving orders
- **orders** - Regular orders
- **inventories** - Stock management

## Troubleshooting

### Issue: "Driver not found" Error
**Cause:** Using SQLite driver with XAMPP's MySQL
**Fix:** Updated `DB_CONNECTION=mysql` in `.env`

### Issue: Empty Patterns Page
**Cause:** Patterns table not seeded
**Fix:** Ran `php artisan db:seed --class=YakanPatternSeeder`

### Issue: PHP Version Error
**Cause:** XAMPP had PHP 8.0, Laravel needs 8.2+
**Fix:** Updated XAMPP to version with PHP 8.2.12

### Issue: Composer Not Found
**Cause:** Composer not in system PATH
**Fix:** Used full path `C:\ProgramData\ComposerSetup\bin\composer.bat`

## Optional: Apache VirtualHost Setup

If you prefer `http://yakan.local` instead of `http://127.0.0.1:8000`:

### 1. Configure VirtualHost
Edit: `C:\Users\HP\Desktop\xampp\apache\conf\extra\httpd-vhosts.conf`

```apache
<VirtualHost *:80>
    ServerName yakan.local
    DocumentRoot "C:/Users/HP/Desktop/yakan-ecommerce-main/yakan-ecommerce-main/public"
    <Directory "C:/Users/HP/Desktop/yakan-ecommerce-main/yakan-ecommerce-main/public">
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/yakan-error.log"
    CustomLog "logs/yakan-access.log" common
</VirtualHost>
```

### 2. Edit Hosts File
Edit: `C:\Windows\System32\drivers\etc\hosts` (as Administrator)

Add:
```
127.0.0.1   yakan.local
```

### 3. Enable VirtualHost in Apache
Edit: `C:\Users\HP\Desktop\xampp\apache\conf\httpd.conf`

Uncomment:
```apache
Include conf/extra/httpd-vhosts.conf
```

### 4. Restart Apache
Restart Apache in XAMPP Control Panel, then access: http://yakan.local

## Maintenance Commands

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Reset Database
```bash
php artisan migrate:fresh --seed
```

### Check Application Status
```bash
php artisan about
```

## Development Workflow

### Daily Startup:
1. Start XAMPP (Apache + MySQL)
2. Navigate to project directory
3. Run `php artisan serve`
4. Run `npm run dev` (in separate terminal for hot reload)
5. Access http://127.0.0.1:8000

### Before Committing Code:
```bash
composer dump-autoload
php artisan optimize:clear
npm run build
```

## Project Paths

- **Project Root:** `C:\Users\HP\Desktop\yakan-ecommerce-main\yakan-ecommerce-main`
- **XAMPP Root:** `C:\Users\HP\Desktop\xampp`
- **PHP Executable:** `C:\Users\HP\Desktop\xampp\php\php.exe`
- **Public Directory:** `C:\Users\HP\Desktop\yakan-ecommerce-main\yakan-ecommerce-main\public`

## Success Indicators

✅ Composer dependencies installed (vendor folder exists)  
✅ NPM dependencies installed (node_modules folder exists)  
✅ Application key generated in `.env`  
✅ Database `yakan_db` created  
✅ All migrations ran successfully  
✅ Seeders populated data (including 14 Yakan patterns)  
✅ Storage linked  
✅ Assets compiled  
✅ Server running at http://127.0.0.1:8000  

## Notes

- This setup uses **MySQL** instead of **SQLite** to match XAMPP's database server
- All 14 traditional Yakan weaving patterns are now available in custom orders
- The project is configured for local development with `APP_DEBUG=true`
- File uploads use the `public` disk and are accessible via `/storage/*`

---

**Setup Completed:** December 5, 2025  
**Laravel Version:** 11.x  
**PHP Version:** 8.2.12  
**Database:** MySQL (XAMPP)  
**Server:** http://127.0.0.1:8000