# 📱 Open Mobile App - Complete Guide

**Since Your Backend is Deployed on Railway**

Your Yakan app is already configured to connect to your production backend:
```
API: https://yakan-webapp-production.up.railway.app/api/v1
Storage: https://yakan-webapp-production.up.railway.app/storage
```

---

## 🚀 Option 1: Instant Preview (Recommended for Testing)

### Using Expo Go App (Easiest)

**Time Required**: 5 minutes  
**Requirements**: 
- Node.js installed
- Phone with Expo Go app

#### Steps:

1. **Open terminal in project folder:**
```powershell
cd C:\xampp\htdocs\Yakan-WebApp
```

2. **Start development server:**
```powershell
npm start
```

**Output should show:**
```
Expo server running at http://localhost:19000
Scan this QR code to preview on your phone
```

3. **Open Expo Go app on your phone:**
   - iOS: Download "Expo Go" from App Store
   - Android: Download "Expo Go" from Google Play

4. **Scan QR code** shown in terminal with your phone camera

5. **App opens automatically** on your phone! Connected to your deployed production backend.

**Testing Checklist:**
- ✅ Home screen loads with products
- ✅ Can browse products from Railway database
- ✅ Can add items to cart
- ✅ Can checkout and place order
- ✅ Orders appear in admin dashboard

---

## 🎯 Option 2: Build Standalone APK (For Real Deployment)

### Best for: Sharing with others, App Store submission, permanent testing

**Time Required**: ~25 minutes  
**Requirements**:
- Expo account (free)
- EAS CLI
- Internet connection

#### Step 1: Create Expo Account
```powershell
# Visit: https://expo.dev/signup
# Create free account
```

#### Step 2: Install EAS CLI
```powershell
npm install -g eas-cli
```

#### Step 3: Login to Expo
```powershell
eas login
```

#### Step 4: Build APK (Android)
```powershell
cd C:\xampp\htdocs\Yakan-WebApp
eas build --platform android --profile preview
```

**This will:**
- Build standalone APK in Expo's cloud servers
- Return a download link (~10-20 minutes)
- Provide APK file you can install on any Android phone

#### Step 5: Download & Install
1. Wait for build to complete
2. Click download link when ready
3. Transfer APK to your Android phone (any method)
4. Open file manager on phone → Tap APK → Install
5. Open "Yakan App" from home screen

**Note:** First launch may take 30 seconds to load (unpacking app)

---

## 📱 Option 3: Build for iPhone (Advanced)

**Requirements**:
- Apple Developer Account ($99/year)
- Mac computer (or use cloud build)
- iOS device

```powershell
eas build --platform ios --profile production
```

Follow same steps as Android but for iOS.

---

## 🔌 Configuration Check

Your mobile app is already configured for production:

**File:** `src/config/config.js`

```javascript
const getApiBaseUrl = () => {
  // ✅ Pointing to your deployed Railway backend
  return 'https://yakan-webapp-production.up.railway.app/api/v1';
};

const getStorageBaseUrl = () => {
  // ✅ Products images loaded from Railway
  return 'https://yakan-webapp-production.up.railway.app/storage';
};
```

**This means:**
- ✅ No configuration changes needed
- ✅ App automatically uses your deployed backend
- ✅ Works on any device, anywhere

---

## 📊 Quick Comparison

| Method | Time | Easy | Sharing | Permanent |
|--------|------|------|---------|-----------|
| **Expo Go** | 5 min | ⭐⭐⭐ | QR code | Temp |
| **APK Build** | 25 min | ⭐⭐ | File | Permanent |
| **App Store** | Hours | ⭐ | App Store | Official |

---

## 🎬 Step-by-Step For Expo Go (Most Common)

### In Your VS Code Terminal:

```bash
# 1. Navigate to project
cd C:\xampp\htdocs\Yakan-WebApp

# 2. Install dependencies (if needed)
npm install

# 3. Start development server
npm start
```

### Expected Output:
```
✔ Metro bundler started
✔ Expo server running
✔ QR Code displayed in terminal/browser

To use the app on your phone:
1. Open Expo Go app
2. Tap "Scan QR code"
3. Point camera at QR code above
```

### On Your Phone:

1. Download **Expo Go** app (free)
2. Open app
3. Tap QR code scanner icon
4. Scan code from terminal
5. Wait 10-20 seconds for app to load
6. **Yakan App opens!**

---

## 🧪 What to Test

Once app is open:

```
Homepage:
├── ✅ Featured products visible
├── ✅ Search bar functional
└── ✅ Navigation tabs working

Products:
├── ✅ Browse all products
├── ✅ Filter by category
├── ✅ See product images from Railway
└── ✅ Tap product for details

Shopping:
├── ✅ Add item to cart
├── ✅ Update quantity
├── ✅ View cart total
└── ✅ Proceed to checkout

Checkout:
├── ✅ Enter shipping address
├── ✅ Select payment method
├── ✅ Upload GCash/Bank receipt
└── ✅ Place order

Order Tracking:
├── ✅ See order in history
├── ✅ Track order status
├── ✅ View tracking number
└── ✅ See real-time updates

Auth:
├── ✅ Login with email
├── ✅ Login with Google
├── ✅ Login with Facebook
└── ✅ Register new account
```

---

## 🔧 Troubleshooting

### "Metro bundler failed"
```powershell
# Clear cache and try again
npm start -- --clean
```

### "Can't connect to backend"
- Verify Railway app is running
- Check internet connection
- Confirm API URL in `src/config/config.js`

### "QR code won't scan"
- Make sure Expo Go is installed on phone
- Try clicking link in terminal instead
- Use browser preview: `Press W` in terminal

### "App loads but no products"
- Check Railway backend is running
- Verify database has products
- Check browser console for API errors (Press `J`)

---

## 🚢 Deploying APK to Users

Once you have APK file:

1. **Email APK** to users
2. **Use WhatsApp/Drive** to share file
3. **Upload to Firebase Hosting** for download link
4. **Submit to Google Play Store** for permanent distribution

**For Google Play Store:**
```powershell
eas submit --platform android
```

Then follow Expo's guided submission process.

---

## 📞 Commands Summary

```powershell
# Start development (Expo Go)
npm start

# Build APK
eas build --platform android --profile preview

# Build for production (Android)
eas build --platform android --profile production

# Build for iPhone
eas build --platform ios --profile production

# Check build status
eas build:list

# Submit to stores
eas submit --platform android
eas submit --platform ios

# Clear cache
npm start -- --clean

# View logs
eas build:logs

# Lint code
npm run lint

# Fix linting issues
npm run lint:fix
```

---

## ✨ You're All Set!

Your mobile app is:
- ✅ Connected to deployed Railway backend
- ✅ Ready to test locally with Expo Go
- ✅ Can be built into APK anytime
- ✅ Can be submitted to app stores

**Recommended Next Steps:**
1. Test with Expo Go on your phone
2. Try placing an order
3. Check if it appears in admin dashboard
4. Once satisfied, build APK for sharing
5. Submit to Google Play Store

---

**Last Updated**: February 8, 2026  
**Backend**: Railway (Production)  
**Mobile Config**: Ready for Deployment
