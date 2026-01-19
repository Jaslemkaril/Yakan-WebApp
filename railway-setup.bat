@echo off
echo ========================================
echo   Railway Initial Setup
echo ========================================
echo.

echo [1/5] Installing Railway CLI...
call npm install -g @railway/cli

echo.
echo [2/5] Logging into Railway...
echo (This will open your browser)
call railway login

echo.
echo [3/5] Initializing Railway project...
call railway init

echo.
echo [4/5] Deploying for the first time...
call railway up

echo.
echo ========================================
echo   IMPORTANT: Next Steps
echo ========================================
echo.
echo 1. Go to Railway dashboard: https://railway.app/dashboard
echo 2. Add MySQL database:
echo    - Click "New" -^> "Database" -^> "Add MySQL"
echo.
echo 3. Set environment variables in Railway dashboard:
echo    APP_NAME=Yakan E-commerce
echo    APP_ENV=production
echo    APP_DEBUG=false
echo    DB_CONNECTION=mysql
echo    (Railway will auto-set MySQL variables)
echo.
echo 4. Generate APP_KEY:
call php artisan key:generate --show
echo    ^^ Copy this key and add to Railway as APP_KEY
echo.
echo 5. Run migrations:
call railway run php artisan migrate --force
echo.
echo 6. Create admin user:
call railway run php artisan tinker
echo.
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo View your app: railway open
echo View logs: railway logs
echo.
pause
