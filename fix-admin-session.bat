@echo off
REM Quick deploy script for admin session fix
echo ========================================
echo   Admin Session Fix - Deploy to Railway
echo ========================================
echo.

echo Step 1: Staging changes...
git add .env.railway
git add railway-session-fix.sh
git add ADMIN_SESSION_FIX.md

echo.
echo Step 2: Committing changes...
git commit -m "Fix: Switch to database sessions for Railway admin persistence"

echo.
echo Step 3: Pushing to GitHub...
git push origin main

echo.
echo ========================================
echo   Deployment Started!
echo ========================================
echo.
echo Railway will automatically:
echo  - Detect the push
echo  - Pull latest code
echo  - Run railway.sh (creates sessions table)
echo  - Restart the server
echo.
echo NEXT STEPS:
echo  1. Go to Railway Dashboard
echo  2. Open Variables tab
echo  3. Change: SESSION_DRIVER=database
echo  4. Wait 1-2 minutes for redeploy
echo  5. Clear browser cookies
echo  6. Try logging in and adding product
echo.
echo Full instructions: ADMIN_SESSION_FIX.md
echo.
pause
