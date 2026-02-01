@echo off
cd /d "c:\xampp_new\htdocs\Yakan-WebApp"
echo Pulling latest changes...
git pull origin main --rebase
echo.
echo Pushing to deploy...
git push origin main
echo.
echo Done! Railway will auto-deploy your changes.
pause
