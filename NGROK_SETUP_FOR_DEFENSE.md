# 🌐 ngrok Setup Guide for Defense Tomorrow

## 📍 Problem
ngrok URL changes every time you restart it. Since you're at home now but defense is at school tomorrow, the URL will change, and your mobile app will fail to connect.

---

## 🔧 Solution: Update ngrok URL Before Defense

### Step 1: Start ngrok at School Tomorrow

When you arrive at school and set up your laptop:

```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve
```

In another terminal:

```powershell
ngrok http 127.0.0.1:8000
```

### Step 2: Copy the NEW ngrok URL

When ngrok starts, look for this line:

```
Forwarding    https://xxxxx-xxxxx-xxxxx.ngrok-free.dev -> http://127.0.0.1:8000
```

**Copy the URL:** `https://xxxxx-xxxxx-xxxxx.ngrok-free.dev` (it will be different from today's)

---

### Step 3: Update Mobile App Config

Open this file:
```
C:\xampp\htdocs\YAKAN-main-main\src\config\config.js
```

Find this line:
```javascript
API_BASE_URL: 'https://preeternal-ungraded-jere.ngrok-free.dev/api/v1',
```

Replace with your NEW URL (example):
```javascript
API_BASE_URL: 'https://new-ngrok-url-xxxxx.ngrok-free.dev/api/v1',
```

⚠️ **Important:** Keep `/api/v1` at the end!

---

### Step 4: Restart Mobile App

```powershell
cd C:\xampp\htdocs\YAKAN-main-main
npm start
```

Press `i` to open in browser, or scan QR code with your phone.

---

## ⏱️ Timeline Tomorrow

| Time | Action |
|------|--------|
| Arrive | Set up laptop, connect to school WiFi |
| 5 min | Start `php artisan serve` |
| 5 min | Start `ngrok http 127.0.0.1:8000` |
| 2 min | Copy new ngrok URL |
| 2 min | Update `src/config/config.js` |
| 2 min | Restart mobile app (`npm start`) |
| 2 min | Test (load products, place order) |
| **DONE!** | Ready to defend! ✅ |

**Total: ~15 minutes**

---

## 🔍 How to Find ngrok URL

When you run `ngrok http 127.0.0.1:8000`, the output looks like:

```
ngrok                                       (Ctrl+C to quit)

Session Status                online
Account                       <your-account>
Version                        3.34.0
Region                         ap (Asia Pacific)
Forwarding                     https://abc123def456.ngrok-free.dev -> http://127.0.0.1:8000
Forwarding                     http://abc123def456.ngrok-free.dev -> http://127.0.0.1:8000

Connections                    ttl    opn     rt1    rt5    p50     p95
                              0      0       0.00   0.00   0.00    0.00
```

**The URL you need:** `https://abc123def456.ngrok-free.dev` (it will be different tomorrow!)

---

## 📝 Quick Checklist

- [ ] Tomorrow: Start Laravel server (`php artisan serve`)
- [ ] Tomorrow: Start ngrok (`ngrok http 127.0.0.1:8000`)
- [ ] Tomorrow: Copy the NEW ngrok URL from ngrok output
- [ ] Tomorrow: Open `src/config/config.js`
- [ ] Tomorrow: Update `API_BASE_URL` with new URL
- [ ] Tomorrow: Restart mobile app (`npm start`)
- [ ] Tomorrow: Test by loading products
- [ ] Tomorrow: Test by placing an order
- [ ] Tomorrow: Demo to teachers! 🎉

---

## 🚨 If Something Goes Wrong

### Problem: Mobile app still can't connect

**Check:**
1. Is ngrok running? (should see "Forwarding" line)
2. Is the URL in `config.js` correct? (copy-paste carefully)
3. Did you restart the mobile app after updating config?

**Fix:**
```powershell
# Stop mobile app (press Ctrl+C in terminal)
# Update config.js
# Restart mobile app (npm start)
```

### Problem: ngrok says "session expired"

**Solution:** Just start ngrok again:
```powershell
ngrok http 127.0.0.1:8000
```

You'll get a new URL. Update config.js again.

### Problem: Laravel server not running

**Check:**
```powershell
cd C:\xampp\htdocs\YAKAN-WEB-main
php artisan serve
```

Should see:
```
Laravel development server started: http://127.0.0.1:8000
```

---

## 💡 Pro Tips

1. **Write it down:** Tomorrow, write down the new ngrok URL on paper before you forget
2. **Screenshot:** Take a screenshot of ngrok output with the URL visible
3. **Double-check:** After updating config.js, open it again to verify the URL was saved
4. **Test early:** Test the connection as soon as you update the URL (don't wait until demo)
5. **Keep terminal open:** Keep the ngrok terminal window open during your entire defense

---

## 📚 Files You'll Need Tomorrow

1. **Terminal 1:** Laravel server
   ```
   cd C:\xampp\htdocs\YAKAN-WEB-main
   php artisan serve
   ```

2. **Terminal 2:** ngrok tunnel
   ```
   ngrok http 127.0.0.1:8000
   ```

3. **Terminal 3:** Mobile app (optional, if presenting from web)
   ```
   cd C:\xampp\htdocs\YAKAN-main-main
   npm start
   ```

4. **File to update:** `C:\xampp\htdocs\YAKAN-main-main\src\config\config.js`

---

## ✅ Before You Leave Home Today

Make sure you have:
- [ ] This guide printed or saved
- [ ] Both projects backed up
- [ ] ngrok installed and working
- [ ] Laravel running
- [ ] Mobile app building successfully

Tomorrow will be smooth! 🚀

---

## 🎯 Summary

**The only thing that changes tomorrow:**
- ngrok URL (everything else stays the same!)

**What to do:**
1. Start ngrok → Get new URL
2. Update config.js → Paste new URL
3. Restart mobile app → Done!

**Time needed:** 5-10 minutes

You got this! 💪
