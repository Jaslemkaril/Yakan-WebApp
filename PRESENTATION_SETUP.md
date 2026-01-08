# Presentation Setup Guide - Quick Start

## 🎯 Goal
Get the Yakan E-commerce mobile app working with the Laravel backend for your presentation.

---

## ⚡ QUICK SETUP (At Home - Working Now)

### Current Configuration:
- **Laptop IP:** `192.168.1.203`
- **Laravel Server:** `http://0.0.0.0:8000`
- **Mobile App API:** `http://192.168.1.203:8000/api/v1`
- **WiFi:** Home network
- **Status:** ✅ WORKING

---

## 🏫 PRESENTATION DAY SETUP

### Option 1: Use School WiFi (RECOMMENDED if stable)

#### Step 1: Connect Both Devices to School WiFi
- Connect your laptop to school WiFi
- Connect your phone to the SAME school WiFi network

#### Step 2: Get Your New IP Address
Open PowerShell on laptop:
```powershell
ipconfig
```
Look for **IPv4 Address** under WiFi adapter (e.g., `192.168.100.50`)

#### Step 3: Update Mobile App Configuration
1. Open `C:\xampp\htdocs\YAKAN-WEB-main\src\config\config.js`
2. Find line 11 and update the IP:
```javascript
const MACHINE_IP = '192.168.100.50'; // ⚠️ UPDATE with new IP from Step 2
```
3. Save the file

#### Step 4: Start MySQL
1. Open XAMPP Control Panel
2. Click **Start** for MySQL
3. Wait until it shows green

#### Step 5: Start Laravel Server
Open PowerShell:
```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve --host=0.0.0.0 --port=8000
```
✅ Should show: "Server running on [http://0.0.0.0:8000]"

#### Step 6: Start Mobile App
Open another PowerShell or Command Prompt:
```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
npm start
```
- Wait for QR code to appear
- Open Expo Go on your phone
- Scan the QR code

#### Step 7: Test Connection
On laptop, open PowerShell:
```powershell
Invoke-WebRequest -Uri "http://YOUR_NEW_IP:8000/api/v1/products" -Method GET
```
✅ Should return status 200

---

### Option 2: Use Mobile Hotspot (MOST RELIABLE)

This is the **SAFEST** option if school WiFi is unstable!

#### Step 1: Create Hotspot on Your Phone
1. Go to Settings → Connections → Mobile Hotspot and Tethering
2. Enable **Mobile Hotspot**
3. Note the **hotspot name** and **password**

#### Step 2: Connect Laptop to Phone's Hotspot
Connect your laptop to the hotspot you just created

#### Step 3: Get Your New IP
On laptop, open PowerShell:
```powershell
ipconfig
```
Look for IPv4 under **WiFi** or **Wireless** (e.g., `192.168.43.1`)

#### Step 4: Update Config
Edit `src\config\config.js`:
```javascript
const MACHINE_IP = '192.168.43.1'; // Your new hotspot IP
```

#### Step 5: Start Everything
```powershell
# Start MySQL in XAMPP Control Panel first

# Then start Laravel
php artisan serve --host=0.0.0.0 --port=8000

# In another terminal, start Expo
npm start
```

#### Step 6: Open App on Test Phone
**Important:** Use a DIFFERENT phone for testing!
- Connect test phone to YOUR hotspot
- Open Expo Go
- Scan QR code

---

### Option 3: USB Tethering (NO WIFI NEEDED)

If WiFi is completely unavailable:

#### Step 1: Connect Phone via USB
1. Connect phone to laptop using USB cable
2. On phone: Settings → Connections → USB tethering → Enable

#### Step 2: Get IP Address
```powershell
ipconfig
```
Look for new network adapter (usually starts with `192.168.42.x`)

#### Step 3: Update Config and Start
Same as Option 2, steps 4-5

---

## 📋 CHECKLIST BEFORE PRESENTATION

### Night Before:
- [ ] Laptop fully charged
- [ ] Phone fully charged
- [ ] USB cable ready
- [ ] XAMPP portable folder copied (backup)
- [ ] Screenshots/video of working app (backup demo)
- [ ] This guide printed or on phone

### At Venue (30 min before):
- [ ] Connect to WiFi or setup hotspot
- [ ] Get new IP address (`ipconfig`)
- [ ] Update `src/config/config.js` with new IP
- [ ] Start MySQL in XAMPP
- [ ] Start Laravel server (`php artisan serve --host=0.0.0.0 --port=8000`)
- [ ] Start Expo (`npm start`)
- [ ] Test on phone - verify products load
- [ ] Test login/register
- [ ] Test cart functionality

---

## 🚨 TROUBLESHOOTING

### "Connection timeout" or "Cannot connect to server"

**Quick Fix:**
```powershell
# 1. Check server is running
netstat -ano | Select-String ":8000"
# Should show: TCP 0.0.0.0:8000

# 2. Check firewall
New-NetFirewallRule -DisplayName "Laravel" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow

# 3. Restart everything
# Stop server (Ctrl+C)
# Close Expo (Ctrl+C)
# Start server again
php artisan serve --host=0.0.0.0 --port=8000
# Start Expo again
npm start
```

### "MySQL not running"
1. Open XAMPP Control Panel
2. Click Start for MySQL
3. Wait for green status

### "Port 8000 already in use"
```powershell
# Find what's using port 8000
netstat -ano | Select-String ":8000"
# Kill the process (use PID from above)
Stop-Process -Id XXXX -Force
```

### Different phone not connecting
- Verify phone is on SAME WiFi/hotspot
- Check phone isn't using mobile data (disable it)
- Verify IP in config matches `ipconfig` output

---

## 💡 PRESENTATION TIPS

### Best Practices:
1. **Arrive 30 minutes early** to setup
2. **Use mobile hotspot** if you're unsure about school WiFi
3. **Have backup plan**: Screenshots or video recording
4. **Test everything** before professor arrives
5. **Keep laptop plugged in** during presentation

### What to Show:
1. **Web Admin Panel** (http://192.168.1.203:8000)
   - Login as admin
   - Show products management
   - Show orders
   - Show chat system

2. **Mobile App** (Expo Go)
   - Browse products
   - Register new user
   - Add to cart
   - Place order
   - Upload payment proof
   - Chat with support

3. **Database** (phpMyAdmin)
   - Show tables
   - Show real-time data updates

---

## 🔄 QUICK REFERENCE - Commands

### Start Everything:
```powershell
# Terminal 1: Laravel Server
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Mobile App
cd C:\xampp\htdocs\YAKAN-WEB-main
npm start
```

### Check IP:
```powershell
ipconfig
```

### Test API:
```powershell
Invoke-WebRequest -Uri "http://YOUR_IP:8000/api/v1/products"
```

### Stop Everything:
- Press `Ctrl+C` in both terminals
- Stop MySQL in XAMPP

---

## 📞 EMERGENCY CONTACTS

If something goes wrong:
1. Breathe! 😊
2. Show backup screenshots/video
3. Explain the architecture using this guide
4. The code is there, even if demo doesn't work live

---

## ✅ FINAL CHECKLIST

**Right before presenting:**
- [ ] MySQL: Green in XAMPP ✅
- [ ] Laravel: "Server running on [http://0.0.0.0:8000]" ✅
- [ ] Expo: QR code visible ✅
- [ ] Phone: Connected to same network ✅
- [ ] Phone: App shows products ✅
- [ ] Test: Can add to cart ✅
- [ ] Test: Can login ✅

**You're ready! Good luck! 🚀**

---

## Current Working Setup (Reference)

```
Home Setup (CURRENTLY WORKING):
├── Laptop IP: 192.168.1.203
├── Network: Home WiFi
├── Laravel: http://0.0.0.0:8000
├── API: http://192.168.1.203:8000/api/v1
└── Phone: Connected to home WiFi
```

Tomorrow, just update the IP and you're good to go!
