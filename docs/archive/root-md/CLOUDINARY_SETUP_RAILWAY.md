# Cloudinary Setup for Railway (Chat Images)

## Why Cloudinary?
Railway's containerized environment doesn't persist files in the public folder between deployments. Cloudinary provides external persistent storage for images uploaded through chat.

## Setup Steps

### 1. Create Free Cloudinary Account
1. Go to https://cloudinary.com/users/register/free
2. Sign up (free tier: 25GB storage, 25GB bandwidth/month)
3. Verify your email

### 2. Get Your Credentials
After login, go to Dashboard:
- **Cloud Name**: (example: `dxxxxx`)
- **API Key**: (example: `123456789012345`)
- **API Secret**: (example: `abcd1234efgh5678`)

### 3. Add to Railway Environment Variables
In your Railway project:
1. Go to **Settings** → **Variables**
2. Add these three variables:
   ```
   CLOUDINARY_CLOUD_NAME = your_cloud_name
   CLOUDINARY_API_KEY = your_api_key
   CLOUDINARY_API_SECRET = your_api_secret
   ```
3. Click **Deploy** to restart with new environment variables

### 4. Verify Setup
1. Wait for deployment to complete (2-3 minutes)
2. Go to your chat page
3. Upload an image
4. Check Laravel logs - should see: `Chat image uploaded to Cloudinary`
5. Image URL should start with `https://res.cloudinary.com/...`

## How It Works

### Upload Flow:
```
User uploads image
    ↓
Try Cloudinary first (if configured)
    ↓ (if Cloudinary fails or not configured)
Fallback to local storage in Railway volume
```

### Controllers Updated:
- `app/Http/Controllers/ChatController.php` - Chat message images
- `app/Http/Controllers/ChatPaymentController.php` - Payment proof images

### Why This Solution?
✅ **Persistent**: Images survive Railway redeployments  
✅ **Fast**: CDN delivery worldwide  
✅ **Free**: 25GB free tier is generous  
✅ **Automatic fallback**: Works without Cloudinary (uses local storage)  
✅ **No code changes needed**: Just add environment variables

## Troubleshooting

### Images still not showing?
1. Check Railway logs for "Cloudinary" messages
2. Verify all 3 environment variables are set correctly
3. Make sure you clicked Deploy after adding variables
4. Old messages may need re-uploading (database has old URLs)

### Cloudinary upload failing?
Check logs for specific error. Common issues:
- Wrong API credentials
- Free tier limit exceeded
- Network timeout (will auto-fallback to local storage)

## Free Tier Limits
- **Storage**: 25GB
- **Bandwidth**: 25GB/month
- **Transformations**: 25,000/month
- **Video**: 0 (images only on free)

For most small businesses, this is more than enough!
