# 🚀 How to Run & Start Everything

## For Tomorrow's Defense at School

Follow these steps in order to get everything running:

---

## Step 1: Start Laravel Backend Server

Open PowerShell and run:

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve
```

**Expected Output:**
```
Laravel development server started: http://127.0.0.1:8000
```

✅ Leave this terminal running!

---

## Step 2: Start ngrok Tunnel

Open a **new PowerShell terminal** and run:

```powershell
ngrok http 127.0.0.1:8000
```

**Expected Output:**
```
Forwarding    https://xxxxx-xxxxx-xxxxx.ngrok-free.dev -> http://127.0.0.1:8000
```

✅ **IMPORTANT:** Copy the ngrok URL (it will be different from today's!)

---

## Step 3: Update Mobile App Config

Open this file:
```
C:\xampp\htdocs\YAKAN-main-main\src\config\config.js
```

Find this line:
```javascript
API_BASE_URL: 'https://preeternal-ungraded-jere.ngrok-free.dev/api/v1',
```

Replace with your NEW ngrok URL from Step 2:
```javascript
API_BASE_URL: 'https://YOUR-NEW-NGROK-URL-HERE.ngrok-free.dev/api/v1',
```

**Example:**
```javascript
API_BASE_URL: 'https://abc123def456ghi.ngrok-free.dev/api/v1',
```

⚠️ **Don't forget `/api/v1` at the end!**

---

## Step 4: Start Mobile App

Open a **new PowerShell terminal** in YAKAN-main-main:

```powershell
cd C:\xampp\htdocs\YAKAN-main-main
npm start
```

**Expected Output:**
```
Expo server running at http://127.0.0.1:19000
```

Press `i` to open in browser, or scan QR code with your phone.

---

## Step 5: Test Everything Works

1. **In mobile app:** Navigate to "Products" or "Shop"
2. **Verify:** Products load with images
3. **Try placing an order:** Add item to cart → Checkout → Place order
4. **Check:** Order appears in admin dashboard

---

## 📊 Terminal Overview

You'll have **3 terminals running:**

| Terminal | Command | Purpose |
|----------|---------|---------|
| Terminal 1 | `php artisan serve` | Laravel backend |
| Terminal 2 | `ngrok http 127.0.0.1:8000` | Public tunnel |
| Terminal 3 | `npm start` | Mobile app |

Keep all 3 running during your demo!

---

## ⏱️ Timeline (10-15 minutes)

| Step | Time | Action |
|------|------|--------|
| 1 | 1 min | Start Laravel server |
| 2 | 1 min | Start ngrok |
| 3 | 2 min | Copy ngrok URL & update config.js |
| 4 | 2 min | Start mobile app |
| 5 | 3 min | Test by placing order |
| 6 | 5 min | Buffer/troubleshooting |

---

## 🔍 Troubleshooting

### Problem: Mobile app can't connect to API

**Solution:**
1. Check ngrok URL in config.js is correct
2. Make sure Laravel server is running
3. Make sure ngrok tunnel is running
4. Restart mobile app (`npm start`)

### Problem: ngrok says "session expired"

**Solution:**
1. Stop ngrok (Ctrl+C)
2. Start it again: `ngrok http 127.0.0.1:8000`
3. Get new URL and update config.js
4. Restart mobile app

### Problem: "Port 8000 is already in use"

**Solution:**
```powershell
# Find what's using port 8000
Get-NetTCPConnection -LocalPort 8000

# Or use a different port
php artisan serve --port=8001
ngrok http 127.0.0.1:8001
```

### Problem: Products don't load

**Solution:**
1. Check Laravel server is running
2. Check ngrok URL in config.js
3. Try refreshing the app

---

## ✅ Quick Checklist

Before demo starts:

- [ ] Laravel server running (`php artisan serve`)
- [ ] ngrok tunnel running (`ngrok http 127.0.0.1:8000`)
- [ ] config.js updated with NEW ngrok URL
- [ ] Mobile app running (`npm start`)
- [ ] Products loading in mobile app
- [ ] Can place an order
- [ ] All 3 terminals visible/running

---

## 📝 What to Show Teachers

1. **Mobile App Flow:**
   - Browse products
   - Add to cart
   - Checkout
   - Place order

2. **Real-time Notification:**
   - Show admin dashboard sees the order instantly
   - Show status updates in real-time

3. **Code:**
   - Show the order model
   - Show the notification service
   - Explain the architecture

---

## 🎯 Demo Script

```
1. "Here's our mobile app with our e-commerce system"
2. "User can browse products and place an order"
3. "When they place an order, the admin gets a real-time notification"
4. "Admin can view order details and update status"
5. "Mobile app sees the status update immediately"
6. "This is powered by our Laravel backend with real-time notifications"
```

---

## 💡 Pro Tips

1. **Test before showing:** Run through the whole flow once before teachers arrive
2. **Have ngrok URL written down:** Write it on paper in case you need to reference it
3. **Keep terminals visible:** During demo, show all 3 terminals running
4. **Have backup plan:** If WiFi fails, you can demo on localhost (same network)
5. **Screenshot success:** Take a screenshot of successful order + notification

---

## 🎉 You're Ready!

Everything is set up. Just follow these 5 steps tomorrow and you'll impress your teachers!

**Good luck with your defense!** 🚀
