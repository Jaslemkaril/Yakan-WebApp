@echo off
echo ========================================
echo   Push to GitHub: Jaslemkaril/Yakan-WebApp
echo ========================================
echo.

echo Step 1: Setting up remote...
git remote remove yakan-webapp 2>nul
git remote add yakan-webapp https://github.com/Jaslemkaril/Yakan-WebApp.git

echo.
echo Step 2: Checking current status...
git status

echo.
echo Step 3: Adding all files...
git add .

echo.
echo Step 4: Committing changes...
git commit -m "Add Railway deployment configuration and setup guides"

echo.
echo ========================================
echo   IMPORTANT: Authentication Required
echo ========================================
echo.
echo You need to authenticate with GitHub.
echo.
echo Choose one option:
echo.
echo [1] Use GitHub Desktop (Easiest)
echo     - Open GitHub Desktop
echo     - Add this repository
echo     - Push to Jaslemkaril/Yakan-WebApp
echo.
echo [2] Use Personal Access Token
echo     - Go to: https://github.com/settings/tokens
echo     - Generate new token (classic)
echo     - Select 'repo' scope
echo     - Copy the token
echo.
echo [3] Use Git Credential Manager
echo     - Will prompt for username/password
echo     - Use Personal Access Token as password
echo.
pause
echo.

echo Attempting to push...
echo.
git push yakan-webapp main

if %errorlevel% neq 0 (
    echo.
    echo ========================================
    echo   Push Failed - Authentication Needed
    echo ========================================
    echo.
    echo Try this command with your Personal Access Token:
    echo.
    echo git push https://YOUR_TOKEN@github.com/Jaslemkaril/Yakan-WebApp.git main
    echo.
    echo Get token from: https://github.com/settings/tokens
    echo.
) else (
    echo.
    echo ========================================
    echo   Success! Files pushed to GitHub
    echo ========================================
    echo.
    echo View at: https://github.com/Jaslemkaril/Yakan-WebApp
    echo.
)

pause
