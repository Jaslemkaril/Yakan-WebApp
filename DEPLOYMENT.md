# Railway Deployment Guide

## Prerequisites
- Railway account (sign up at https://railway.app)
- Git repository

## Initial Setup

### 1. Install Railway CLI
```cmd
npm install -g @railway/cli
```

### 2. Login to Railway
```cmd
railway login
```

### 3. Initialize Project
```cmd
railway init
```

### 4. Add MySQL Database
In Railway dashboard:
- Click "New" → "Database" → "Add MySQL"
- Railway will automatically set DATABASE_URL environment variable

### 5. Set Environment Variables
In Railway dashboard, add these variables:
```
APP_NAME=Yakan E-commerce
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Generate APP_KEY
```cmd
php artisan key:generate --show
```
Copy the output and set it as APP_KEY in Railway.

### 7. Deploy
```cmd
railway up
```

## Update/Redeploy After Changes

### Method 1: Using Railway CLI
```cmd
git add .
git commit -m "Your update message"
git push
railway up
```

### Method 2: Automatic Git Deployment
1. In Railway dashboard, connect your GitHub repository
2. Enable auto-deploy
3. Every push to main branch will auto-deploy:
```cmd
git add .
git commit -m "Updated features"
git push origin main
```

## Post-Deployment Tasks

### Run Migrations
```cmd
railway run php artisan migrate --force
```

### Clear Cache
```cmd
railway run php artisan cache:clear
railway run php artisan config:clear
railway run php artisan view:clear
```

### Create Admin User
```cmd
railway run php artisan tinker
# Then run your user creation script
```

## Storage Setup

### Link Storage
```cmd
railway run php artisan storage:link
```

### For File Uploads
Consider using Railway Volumes or external storage (AWS S3, Cloudinary):
1. In Railway dashboard: Settings → Volumes → Add Volume
2. Mount path: `/app/storage/app/public`

## Monitoring

### View Logs
```cmd
railway logs
```

### Check Deployment Status
```cmd
railway status
```

## Troubleshooting

### If deployment fails:
1. Check logs: `railway logs`
2. Verify environment variables in Railway dashboard
3. Ensure APP_KEY is set
4. Check database connection

### Common Issues:
- **500 Error**: Check APP_KEY and .env variables
- **Database Error**: Verify MySQL credentials
- **Storage Error**: Run `php artisan storage:link`
- **Permission Error**: Check file permissions in storage/

## Production Checklist
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] APP_KEY generated
- [ ] Database configured
- [ ] Mail settings configured
- [ ] Storage linked
- [ ] Migrations run
- [ ] Admin user created
- [ ] HTTPS enabled (automatic on Railway)

## Useful Commands

```cmd
# Deploy
railway up

# View logs
railway logs

# Run artisan commands
railway run php artisan [command]

# Open app in browser
railway open

# Check service status
railway status

# Link to existing project
railway link
```

## Cost Optimization
- Railway free tier: $5 credit/month
- Estimated cost: ~$5-10/month for small apps
- Monitor usage in Railway dashboard

## Support
- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
