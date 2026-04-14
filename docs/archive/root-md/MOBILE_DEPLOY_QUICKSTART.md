# 🚀 Mobile App Quick Start

## Your Current Status

✅ **Backend Deployed**: https://yakan-webapp-production.up.railway.app  
✅ **Mobile App Config**: Already pointing to production API  
❌ **Mobile App Build**: Not created yet  

---

## Fastest Path to APK (For Thesis Demo)

**Total Time: ~30 minutes**

### Step 1: Install EAS CLI (2 minutes)
```powershell
npm install -g eas-cli
```

### Step 2: Login to Expo (1 minute)
```powershell
eas login
```
*Create free account if needed: https://expo.dev/signup*

### Step 3: Build APK (20-25 minutes)
```powershell
eas build --platform android --profile preview
```

### Step 4: Download & Install
- EAS will provide download link when build completes
- Transfer APK to Android phone
- Install and test!

---

## OR Use Our Automated Script

```powershell
.\deploy-mobile.ps1
```

This script will:
- ✓ Check prerequisites
- ✓ Install EAS CLI if needed
- ✓ Guide you through login
- ✓ Start the build process
- ✓ Provide download link

---

## Build Costs

| Build Type | Cost | Build Time |
|------------|------|------------|
| **Preview (APK)** | FREE | 10-20 min |
| **Production (AAB)** | FREE* | 15-25 min |

*Free tier: 30 builds/month for iOS, unlimited for Android*

---

## What Happens During Build?

1. **Upload** - Your code uploads to Expo's cloud servers
2. **Build** - Expo builds native Android APK/AAB
3. **Download** - You get a link to download the file
4. **Install** - Install APK on any Android device

No Android Studio or complex setup needed!

---

## After Building

### Testing Checklist
- [ ] Install APK on Android device
- [ ] Test Google/Facebook login
- [ ] Browse products from Railway backend
- [ ] Add items to cart
- [ ] Complete checkout with GCash/Bank receipt upload
- [ ] Track order status

---

## Troubleshooting

**Build fails?**
```powershell
eas build --platform android --profile preview --clear-cache
```

**Need to check build status?**
```powershell
eas build:list
```

**Want to build locally instead?**
See: [MOBILE_APP_DEPLOYMENT.md](MOBILE_APP_DEPLOYMENT.md) - Option 2

---

## Important Notes

⚠️ **Your app is already configured!**
- API points to: `https://yakan-webapp-production.up.railway.app/api/v1`
- Package name: `com.yakan.app`
- All permissions set correctly

✅ **No code changes needed** - just build and deploy!

---

## Full Documentation

For detailed deployment options and store submission:
👉 See [MOBILE_APP_DEPLOYMENT.md](MOBILE_APP_DEPLOYMENT.md)

---

## Need Help?

1. Check build logs in Expo dashboard
2. Review [Expo Docs](https://docs.expo.dev/build/introduction/)
3. Test backend API: `curl https://yakan-webapp-production.up.railway.app/api/v1/products`
