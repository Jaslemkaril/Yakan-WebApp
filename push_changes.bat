@echo off
cd /d C:\xamppp\htdocs\Yakan-WebApp
set GIT_PAGER=
git config core.pager ""
echo Adding files...
git add .
echo Committing...
git commit -m "Fix chat image uploads to use persistent storage"
echo Pushing...
git push origin main
echo Done!
pause
