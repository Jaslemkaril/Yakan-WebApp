# 📱 Mobile App Deployment Guide

## Current Status
- ✅ **Backend**: Deployed on Railway (`https://yakan-webapp-production.up.railway.app`)
- ✅ **API Configuration**: Mobile app already pointing to production
- ❌ **Mobile App**: Not deployed (only runs in Expo Go)

---

## Deployment Options

### Option 1: EAS Build (Recommended for Production) ⭐

Build standalone APK/AAB for Android and IPA for iOS using Expo Application Services.

#### **Prerequisites**
- Expo account (free tier available)
- Node.js installed
- Android/iOS developer accounts (for store deployment)

#### **Step 1: Install EAS CLI**
```powershell
npm install -g eas-cli
```

#### **Step 2: Login to Expo**
```powershell
eas login
```

#### **Step 3: Configure EAS**
```powershell
eas build:configure
```

This creates an `eas.json` file. Update it:

```json
{
  "cli": {
    "version": ">= 5.0.0"
  },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal",
      "android": {
        "gradleCommand": ":app:assembleDebug"
      },
      "ios": {
        "buildConfiguration": "Debug"
      }
    },
    "preview": {
      "distribution": "internal",
      "android": {
        "buildType": "apk"
      }
    },
    "production": {
      "android": {
        "buildType": "app-bundle"
      },
      "ios": {
        "autoIncrement": true
      }
    }
  },
  "submit": {
    "production": {}
  }
}
```

#### **Step 4: Update app.json**
Add package name and bundle identifier:

```json
{
  "expo": {
    "name": "Yakan App",
    "slug": "yakan-app",
    "version": "1.0.0",
    "android": {
      "package": "com.yakan.app",
      "versionCode": 1,
      "adaptiveIcon": {
        "foregroundImage": "./assets/adaptive-icon.png",
        "backgroundColor": "#ffffff"
      }
    },
    "ios": {
      "bundleIdentifier": "com.yakan.app",
      "buildNumber": "1.0.0",
      "supportsTablet": true
    }
  }
}
```

#### **Step 5: Build for Android**

**For testing (APK):**
```powershell
eas build --platform android --profile preview
```

**For Google Play Store (AAB):**
```powershell
eas build --platform android --profile production
```

This process:
- ✅ Uploads your code to Expo's servers
- ✅ Builds the app in the cloud
- ✅ Provides download link when complete (~10-20 minutes)

#### **Step 6: Build for iOS** (Requires Apple Developer Account - $99/year)
```powershell
eas build --platform ios --profile production
```

#### **Step 7: Download and Test**
- EAS will provide a download link
- Install APK on Android device for testing
- Use TestFlight for iOS testing

#### **Step 8: Submit to Stores**

**Google Play Store:**
```powershell
eas submit --platform android
```

**Apple App Store:**
```powershell
eas submit --platform ios
```

---

### Option 2: Local Build (No Cloud Build Needed)

Build APK locally on your Windows machine.

#### **Prerequisites**
- Android Studio installed
- JDK 17+ installed
- Environment variables configured

#### **Step 1: Install Android Studio**
1. Download from: https://developer.android.com/studio
2. Install Android SDK
3. Set environment variables:
   - `ANDROID_HOME=C:\Users\YourName\AppData\Local\Android\Sdk`
   - Add to PATH: `%ANDROID_HOME%\platform-tools`

#### **Step 2: Eject from Expo (Creates native Android/iOS folders)**
```powershell
npx expo prebuild
```

This creates `android/` and `ios/` folders.

#### **Step 3: Build APK**
```powershell
cd android
./gradlew assembleRelease
```

APK location: `android/app/build/outputs/apk/release/app-release.apk`

#### **Step 4: Sign APK (For Distribution)**
```powershell
# Generate keystore
keytool -genkey -v -keystore yakan-release-key.keystore -alias yakan-key -keyalg RSA -keysize 2048 -validity 10000
```

Add to `android/gradle.properties`:
```properties
YAKAN_UPLOAD_STORE_FILE=yakan-release-key.keystore
YAKAN_UPLOAD_KEY_ALIAS=yakan-key
YAKAN_UPLOAD_STORE_PASSWORD=your_password
YAKAN_UPLOAD_KEY_PASSWORD=your_password
```

Update `android/app/build.gradle`:
```gradle
android {
    signingConfigs {
        release {
            storeFile file(YAKAN_UPLOAD_STORE_FILE)
            storePassword YAKAN_UPLOAD_STORE_PASSWORD
            keyAlias YAKAN_UPLOAD_KEY_ALIAS
            keyPassword YAKAN_UPLOAD_KEY_PASSWORD
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            shrinkResources true
        }
    }
}
```

Rebuild:
```powershell
./gradlew assembleRelease
```

---

### Option 3: Expo Publish (Simplest - OTA Updates)

Publish to Expo's platform for instant updates without rebuilding.

#### **How it works:**
- Users download Expo Go app
- Scan QR code or use link
- App loads from Expo servers
- Updates push instantly (no app store approval)

#### **Limitations:**
- ❌ Users must have Expo Go installed
- ❌ Not suitable for production/end users
- ❌ Cannot submit to Google Play or App Store
- ✅ Good for testing and demos

#### **Steps:**
```powershell
# Build production bundle
expo export

# Publish to Expo (legacy)
# Note: expo publish is deprecated, use EAS Update instead
eas update --branch production
```

---

## Recommended Deployment Strategy

### **For Academic/Thesis Demo:**
1. ✅ **Use Option 1 (EAS Build - Preview)**
   - Build APK using EAS
   - Share APK file with evaluators
   - No store submission needed
   - Professional standalone app

```powershell
# Quick deployment for demo
eas build --platform android --profile preview
```

### **For Production Launch:**
1. ✅ **Use Option 1 (EAS Build - Production)**
   - Submit to Google Play Store
   - Submit to Apple App Store (if iOS needed)
   - Professional deployment

---

## Update Config Before Building

Your `src/config/config.js` already points to production:

```javascript
return 'https://yakan-webapp-production.up.railway.app/api/v1';
```

✅ **No changes needed!**

---

## Costs

| Option | Cost | Best For |
|--------|------|----------|
| **EAS Build (Free Tier)** | FREE | Testing, demos, thesis |
| **EAS Build (Paid)** | $29/month | Unlimited builds |
| **Google Play Store** | **$25 one-time** | Production release |
| **Apple App Store** | $99/year | iOS production |
| **Local Build** | FREE | Full control, no cloud |

---

## Quick Start for Thesis Demo

**Fastest path to working APK:**

```powershell
# 1. Install EAS CLI
npm install -g eas-cli

# 2. Login
eas login

# 3. Configure
eas build:configure

# 4. Build APK
eas build --platform android --profile preview

# 5. Wait 10-20 minutes, download APK from link
# 6. Install on Android device and demo!
```

---

## Testing Your Build

### **Before Building:**
1. ✅ Backend is live: https://yakan-webapp-production.up.railway.app
2. ✅ Test API endpoints:
   ```powershell
   curl https://yakan-webapp-production.up.railway.app/api/v1/products
   ```
3. ✅ Verify config.js points to production URL

### **After Building:**
1. Install APK on Android device
2. Test key flows:
   - Login/Register (Google/Facebook OAuth)
   - Browse products
   - Add to cart
   - Checkout process
   - Payment with receipt upload
   - Track orders

---

## Troubleshooting

### **Build Fails**
```powershell
# Clear cache and retry
npx expo prebuild --clean
eas build --platform android --profile preview --clear-cache
```

### **API Connection Issues**
- Verify Railway backend is running
- Check CORS settings in Laravel
- Test API with Postman/curl

### **OAuth Not Working**
- Update Facebook App settings with new app package name
- Update Google OAuth console with package name
- Add SHA-1 fingerprint for Android

---

## Next Steps

1. **Choose deployment option** (Option 1 recommended)
2. **Create Expo account** (if using EAS)
3. **Update app.json** with package details
4. **Build APK** for testing
5. **Test on physical device**
6. **Submit to Play Store** (optional - for production)

---

## Support Resources

- **Expo Docs**: https://docs.expo.dev/build/setup/
- **EAS Build**: https://docs.expo.dev/build/introduction/
- **Play Store Guide**: https://docs.expo.dev/submit/android/
- **App Store Guide**: https://docs.expo.dev/submit/ios/

---

**Need help?** Check the build logs in EAS dashboard or run with `--verbose` flag.
