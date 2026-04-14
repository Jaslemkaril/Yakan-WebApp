# MySQL max_allowed_packet Fix Guide

## The Problem
You're getting this error:
```
SQLSTATE[08S01]: Communication link failure: 1153 Got a packet bigger than 'max_allowed_packet' bytes
```

This happens when session data (like base64-encoded images) exceeds MySQL's packet size limit.

## Immediate Fix Applied
✅ Changed `SESSION_DRIVER=database` to `SESSION_DRIVER=file` in `.env`

This is the **recommended solution** as file sessions handle large data better.

---

## Alternative: Increase MySQL Packet Size

If you prefer to keep database sessions, follow these steps:

### Option 1: Temporary Fix (Current Session Only)
Run this SQL command in phpMyAdmin or MySQL CLI:
```sql
SET GLOBAL max_allowed_packet=67108864;  -- 64MB
```
Or run the provided SQL file:
```bash
mysql -u root -p yakan_db < fix-mysql-packet-size.sql
```

### Option 2: Permanent Fix

#### For XAMPP Users:
1. Open `C:\Users\HP\Desktop\xampp\mysql\bin\my.ini`
2. Find the `[mysqld]` section
3. Add or modify this line:
   ```ini
   max_allowed_packet=64M
   ```
4. Restart MySQL from XAMPP Control Panel

#### For Standalone MySQL:
1. Locate `my.cnf` or `my.ini` (usually in MySQL installation directory)
2. Add under `[mysqld]`:
   ```ini
   max_allowed_packet=64M
   ```
3. Restart MySQL service

### Verify the Fix:
Run this SQL query:
```sql
SHOW VARIABLES LIKE 'max_allowed_packet';
```
Should show: `67108864` (64MB)

---

## Best Practice for Custom Orders
Instead of storing large images in sessions, consider:

1. **Use file sessions** (already implemented) ✅
2. **Store images directly to disk** during upload
3. **Store only file paths** in sessions, not base64 data
4. **Use temporary files** for custom order processing

---

## Testing
After applying the fix:
1. Clear your browser cookies
2. Clear Laravel cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan session:clear
   ```
3. Try uploading a custom order again

---

## Current Configuration
- Session Driver: **file** (recommended for large data)
- Session Lifetime: 120 minutes
- Session Path: `storage/framework/sessions`
