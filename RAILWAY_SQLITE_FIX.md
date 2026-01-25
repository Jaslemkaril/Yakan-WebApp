# Railway SQLite Configuration Fix

## Problem Solved
Fixed "Connection refused (MySQL)" error by forcing SQLite database.

## Configuration

### Environment Variables (Set in Railway)
```
DB_CONNECTION=sqlite
DB_DATABASE=/app/storage/database.sqlite
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=file
CACHE_STORE=file
```

### File Structure
```
/app/
├── storage/
│   ├── database.sqlite       # Main database (auto-created)
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
```

## Verification Steps

1. **Check database connection:**
   ```
   Visit: /debug/db (when APP_DEBUG=true)
   ```

2. **Seed database if needed:**
   ```
   Visit: /debug/seed (when APP_DEBUG=true)
   ```

3. **Check homepage:**
   ```
   Should show products without errors
   ```

## Troubleshooting

### If still seeing MySQL error:
1. Clear Railway config cache:
   - Go to Railway Dashboard
   - Click "Restart" 
   - Config cache should clear on restart

2. Verify environment variables:
   - Check Railway Variables tab
   - Ensure `DB_CONNECTION=sqlite`

### If database is empty:
```bash
# Run in Railway shell
php artisan migrate --force
php artisan db:seed --force
```

## Important Notes

- ✅ SQLite database persists in `/app/storage/database.sqlite`
- ✅ Automatic seeding if database is empty
- ✅ Proper permissions set on startup
- ❌ Do NOT use MySQL in Railway (not provisioned)
