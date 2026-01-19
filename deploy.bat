@echo off
echo ========================================
echo   Yakan E-commerce - Railway Deployment
echo ========================================
echo.

echo [1/4] Adding changes to git...
git add .

echo.
set /p commit_msg="Enter commit message: "
git commit -m "%commit_msg%"

echo.
echo [2/4] Pushing to repository...
git push origin main

echo.
echo [3/4] Deploying to Railway...
railway up

echo.
echo [4/4] Running post-deployment tasks...
railway run php artisan migrate --force
railway run php artisan config:cache
railway run php artisan route:cache
railway run php artisan view:cache

echo.
echo ========================================
echo   Deployment Complete!
echo ========================================
echo.
echo View your app: railway open
echo View logs: railway logs
echo.
pause
