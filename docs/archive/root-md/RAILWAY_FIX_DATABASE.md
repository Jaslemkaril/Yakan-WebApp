# Railway MySQL Database Setup Guide

## Quick Fix for Database Connection Issues

If you're experiencing database connection errors on Railway, follow this guide to set up MySQL properly.

## Step 1: Add MySQL Database Service

1. Go to your Railway project dashboard
2. Click **"New"** button (top right)
3. Select **"Database"**
4. Choose **"Add MySQL"**
5. Wait for MySQL to provision (takes ~30 seconds)

## Step 2: Verify Database Variables

Railway automatically creates these variables when you add MySQL:
- `MYSQLHOST` - Database host address
- `MYSQLPORT` - Database port (usually 3306)
- `MYSQLDATABASE` - Database name
- `MYSQLUSER` - Database username
- `MYSQLPASSWORD` - Database password

You can view these in your MySQL service's **Variables** tab.

## Step 3: Configure Your Web Service

Click on your **web service** → **Variables** tab → **Raw Editor**, and ensure you have:

```env
APP_NAME=Yakan E-commerce
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**IMPORTANT:** 
- Use `${{VARIABLE}}` with **double curly braces**, not `${VARIABLE}`
- Railway will automatically replace these with actual values
- Make sure `APP_KEY` is set (generate with `php artisan key:generate`)

## Step 4: Generate Public Domain (if needed)

1. Go to your web service's **Settings** tab
2. Scroll to **"Networking"** section
3. Click **"Generate Domain"**
4. Copy the generated domain (e.g., `your-app-production-xxxx.up.railway.app`)

## Step 5: Deploy and Verify

1. Railway will automatically redeploy after saving environment variables
2. Check **Deploy Logs** tab for deployment progress
3. Look for these success indicators:
   - ✅ "Migration table created successfully"
   - ✅ "Migrating: [migration names]"
   - ✅ "Application ready"
   - ✅ No database connection errors

## Common Issues and Solutions

### Issue: "SQLSTATE[HY000] [2002] Connection refused"
**Solution**: Ensure MySQL service is running and environment variables are correctly set.

### Issue: "Access denied for user"
**Solution**: Verify that `DB_USERNAME` and `DB_PASSWORD` are using the correct Railway variables:
```env
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}
```

### Issue: "Unknown database"
**Solution**: Check that `DB_DATABASE` variable is set:
```env
DB_DATABASE=${{MYSQLDATABASE}}
```

### Issue: Migrations not running
**Solution**: Add a start command in Railway settings or Procfile:
```
web: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

## Verifying Database Connection

After deployment, you can verify the database connection using Railway's logs:

1. Go to **Deployments** tab
2. Click on the latest deployment
3. Check logs for database connection messages
4. You should see successful migration output

## Database Management

### Running Migrations Manually

If you need to run migrations manually:
1. Use Railway CLI: `railway run php artisan migrate --force`
2. Or connect to your service and run the command

### Viewing Database Tables

You can connect to your MySQL database using:
- Railway's built-in database viewer
- External tools like MySQL Workbench or DBeaver
- Connection string available in MySQL service variables

### Database Backups

Railway provides automatic backups for MySQL databases. You can also create manual backups:
1. Go to your MySQL service
2. Click on **Backups** tab
3. Create a manual backup before major changes

## Production Best Practices

1. **Environment Separation**: Use different databases for production and staging
2. **Regular Backups**: Schedule regular database backups
3. **Monitor Performance**: Keep an eye on database performance metrics in Railway dashboard
4. **Secure Credentials**: Never hardcode database credentials; always use Railway's environment variables
5. **Connection Pooling**: Laravel handles this automatically, but monitor connection usage

## Troubleshooting Checklist

- [ ] MySQL service is running in Railway
- [ ] Environment variables are set correctly with `${{}}` syntax
- [ ] Web service is linked to MySQL service
- [ ] Database migrations ran successfully
- [ ] No connection errors in deployment logs
- [ ] Public domain is generated and set in `APP_URL`

## Need More Help?

- Check Railway's [MySQL Documentation](https://docs.railway.app/databases/mysql)
- Review Laravel's [Database Documentation](https://laravel.com/docs/database)
- Check deployment logs for specific error messages
- Ensure all environment variables are properly configured
