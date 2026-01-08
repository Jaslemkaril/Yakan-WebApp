# Mobile App Connection Troubleshooting Guide

## Problem: "Request timeout" or "Cannot connect to server"

### Quick Fixes (Try these first!)

#### 1. **Are you using Android Emulator or Physical Device?**

**For Android Emulator:**
- Edit `src/config/config.js`
- In the `getApiBaseUrl()` function, uncomment this line:
  ```javascript
  return `http://10.0.2.2:${PORT}/api/v1`; // Android Emulator
  ```
- Comment out the physical device line

**For Physical Android Device:**
- Keep the default setting (already configured)
- Make sure your phone and computer are on the **SAME WiFi network**

#### 2. **Restart the Laravel Server with Network Access**

Open PowerShell and run:
```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve --host=0.0.0.0 --port=8000
```

**Important:** Use `--host=0.0.0.0` to allow network connections (not just localhost)!

#### 3. **Check Your Computer's IP Address**

In PowerShell, run:
```powershell
ipconfig
```

Look for your **IPv4 Address** under your WiFi adapter (e.g., `192.168.1.203`)

Then update `src/config/config.js`:
```javascript
const MACHINE_IP = '192.168.1.203'; // ⚠️ UPDATE THIS
```

#### 4. **Allow Port 8000 in Windows Firewall**

In PowerShell (Run as Administrator):
```powershell
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
```

#### 5. **Rebuild the Expo App**

After changing `app.json`, you MUST rebuild:
```bash
npm start --clear
# Then press 'a' for Android
```

Or stop the app completely and restart it.

---

## Verification Steps

### Test 1: Can your computer access the API?
In PowerShell:
```powershell
Invoke-WebRequest -Uri "http://192.168.1.203:8000/api/v1/products" -Method GET
```
✓ Should return status 200

### Test 2: Can your phone access the API?
On your phone's browser, open:
```
http://192.168.1.203:8000/api/v1/products
```
✓ Should show JSON data

### Test 3: Are you on the same network?
- Check your computer's WiFi network name
- Check your phone's WiFi network name
- ✓ They must match exactly!

---

## Common Issues

### "Connection refused" or "Network request failed"
**Cause:** Laravel server not accessible from network
**Fix:** Start server with `--host=0.0.0.0`

### "Request timeout" (takes 60 seconds then fails)
**Cause:** Wrong IP address or firewall blocking
**Fix:** 
1. Double-check IP in config.js
2. Add firewall rule
3. Restart Laravel server

### Works in browser but not in app
**Cause:** Android blocking HTTP cleartext traffic
**Fix:** Already fixed in `app.json` with `usesCleartextTraffic: true`
- Must rebuild app after this change!

### Using Android Emulator
**Cause:** Emulator needs special IP `10.0.2.2`
**Fix:** Uncomment emulator line in `src/config/config.js`

---

## Still Not Working?

### Nuclear Option - Use ngrok (Temporary Internet URL)

1. Install ngrok: https://ngrok.com/download
2. Run ngrok:
   ```bash
   ngrok http 8000
   ```
3. Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)
4. Update `src/config/config.js`:
   ```javascript
   return `https://abc123.ngrok.io/api/v1`; // Use your ngrok URL
   ```
5. Restart the app

This works from anywhere, even over mobile data!

---

## Current Configuration

- **API Base URL:** `http://192.168.1.203:8000/api/v1`
- **Storage Base URL:** `http://192.168.1.203:8000/storage`
- **Request Timeout:** 60 seconds
- **Cleartext Traffic:** Enabled ✓
- **Network Permissions:** Enabled ✓
