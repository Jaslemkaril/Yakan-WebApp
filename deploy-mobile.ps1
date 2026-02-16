# Yakan Mobile App - Quick Deploy Script
# This script helps you deploy your mobile app using EAS Build

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   YAKAN MOBILE APP - DEPLOYMENT SCRIPT" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Check if Node.js is installed
Write-Host "[1/6] Checking Node.js installation..." -ForegroundColor Yellow
try {
    $nodeVersion = node --version
    Write-Host "✓ Node.js is installed: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js is NOT installed!" -ForegroundColor Red
    Write-Host "Please install Node.js from: https://nodejs.org/" -ForegroundColor Red
    exit 1
}

# Check if EAS CLI is installed
Write-Host ""
Write-Host "[2/6] Checking EAS CLI installation..." -ForegroundColor Yellow
$easInstalled = Get-Command eas -ErrorAction SilentlyContinue

if (-not $easInstalled) {
    Write-Host "✗ EAS CLI not found. Installing..." -ForegroundColor Yellow
    npm install -g eas-cli
    Write-Host "✓ EAS CLI installed successfully!" -ForegroundColor Green
} else {
    $easVersion = eas --version
    Write-Host "✓ EAS CLI is installed: $easVersion" -ForegroundColor Green
}

# Check if logged in to Expo
Write-Host ""
Write-Host "[3/6] Checking Expo account..." -ForegroundColor Yellow
Write-Host "Please login to your Expo account:" -ForegroundColor Cyan
eas login

# Configure EAS (if not already configured)
Write-Host ""
Write-Host "[4/6] Configuring EAS Build..." -ForegroundColor Yellow
if (Test-Path "eas.json") {
    Write-Host "✓ eas.json already exists" -ForegroundColor Green
} else {
    Write-Host "Creating eas.json configuration..." -ForegroundColor Yellow
    eas build:configure
}

# Display build options
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   SELECT BUILD TYPE" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Preview Build (APK for testing) - RECOMMENDED for thesis demo" -ForegroundColor Green
Write-Host "2. Production Build (AAB for Play Store)" -ForegroundColor Yellow
Write-Host "3. Both Android and iOS (Production)" -ForegroundColor Yellow
Write-Host "4. Cancel" -ForegroundColor Red
Write-Host ""

$choice = Read-Host "Enter your choice (1-4)"

switch ($choice) {
    "1" {
        Write-Host ""
        Write-Host "[5/6] Building Preview APK for Android..." -ForegroundColor Yellow
        Write-Host "This will take 10-20 minutes. Please wait..." -ForegroundColor Cyan
        Write-Host ""
        eas build --platform android --profile preview
        
        Write-Host ""
        Write-Host "================================================" -ForegroundColor Green
        Write-Host "   BUILD COMPLETE!" -ForegroundColor Green
        Write-Host "================================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "✓ Download the APK from the link above" -ForegroundColor Green
        Write-Host "✓ Install it on your Android device" -ForegroundColor Green
        Write-Host "✓ Test your app!" -ForegroundColor Green
        Write-Host ""
    }
    "2" {
        Write-Host ""
        Write-Host "[5/6] Building Production AAB for Google Play Store..." -ForegroundColor Yellow
        Write-Host "This will take 10-20 minutes. Please wait..." -ForegroundColor Cyan
        Write-Host ""
        eas build --platform android --profile production
        
        Write-Host ""
        Write-Host "================================================" -ForegroundColor Green
        Write-Host "   BUILD COMPLETE!" -ForegroundColor Green
        Write-Host "================================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Yellow
        Write-Host "1. Download the AAB file" -ForegroundColor White
        Write-Host "2. Go to Google Play Console" -ForegroundColor White
        Write-Host "3. Upload the AAB to create a release" -ForegroundColor White
        Write-Host ""
    }
    "3" {
        Write-Host ""
        Write-Host "[5/6] Building Production builds for Android and iOS..." -ForegroundColor Yellow
        Write-Host "This will take 20-40 minutes. Please wait..." -ForegroundColor Cyan
        Write-Host ""
        eas build --platform all --profile production
        
        Write-Host ""
        Write-Host "================================================" -ForegroundColor Green
        Write-Host "   BUILDS COMPLETE!" -ForegroundColor Green
        Write-Host "================================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Yellow
        Write-Host "1. Download both AAB (Android) and IPA (iOS) files" -ForegroundColor White
        Write-Host "2. Submit to Google Play Store (Android)" -ForegroundColor White
        Write-Host "3. Submit to Apple App Store (iOS)" -ForegroundColor White
        Write-Host ""
    }
    "4" {
        Write-Host ""
        Write-Host "Build cancelled." -ForegroundColor Yellow
        Write-Host ""
        exit 0
    }
    default {
        Write-Host ""
        Write-Host "Invalid choice. Exiting..." -ForegroundColor Red
        Write-Host ""
        exit 1
    }
}

Write-Host "[6/6] Deployment process completed!" -ForegroundColor Green
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   USEFUL COMMANDS" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "View build status:  eas build:list" -ForegroundColor White
Write-Host "Submit to stores:   eas submit" -ForegroundColor White
Write-Host "Run locally:        npm start" -ForegroundColor White
Write-Host ""
Write-Host "Need help? Check MOBILE_APP_DEPLOYMENT.md" -ForegroundColor Cyan
Write-Host ""
