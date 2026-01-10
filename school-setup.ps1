# YAKAN E-COMMERCE - SCHOOL PRESENTATION SETUP
# Run this script when you arrive at school

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  YAKAN E-COMMERCE - SCHOOL SETUP" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Get current IP
Write-Host "STEP 1: Getting your IP address..." -ForegroundColor Green
$ip = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi*" | Select-Object -First 1).IPAddress
Write-Host "Your IP: $ip" -ForegroundColor Cyan
Write-Host ""

# Step 2: Show config file location
Write-Host "STEP 2: Update config file with this IP" -ForegroundColor Green
Write-Host "File: src\config\config.js" -ForegroundColor Yellow
Write-Host "Line 11: const MACHINE_IP = '$ip';" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press any key after you've updated the config file..." -ForegroundColor Yellow
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Step 3: Check MySQL
Write-Host ""
Write-Host "STEP 3: Checking MySQL..." -ForegroundColor Green
$mysqlRunning = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlRunning) {
    Write-Host "✓ MySQL is running!" -ForegroundColor Green
} else {
    Write-Host "✗ MySQL is NOT running!" -ForegroundColor Red
    Write-Host "  Please start MySQL in XAMPP Control Panel" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Press any key after starting MySQL..." -ForegroundColor Yellow
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}

# Step 4: Check if port 8000 is free
Write-Host ""
Write-Host "STEP 4: Checking port 8000..." -ForegroundColor Green
$portInUse = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if ($portInUse) {
    Write-Host "✗ Port 8000 is already in use!" -ForegroundColor Red
    Write-Host "  Attempting to free port 8000..." -ForegroundColor Yellow
    $process = Get-Process -Id $portInUse.OwningProcess -ErrorAction SilentlyContinue
    if ($process) {
        Stop-Process -Id $process.Id -Force
        Write-Host "✓ Port freed!" -ForegroundColor Green
    }
} else {
    Write-Host "✓ Port 8000 is available!" -ForegroundColor Green
}

# Step 5: Test API
Write-Host ""
Write-Host "STEP 5: Ready to start servers!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Open a NEW PowerShell window" -ForegroundColor Cyan
Write-Host "  2. Run: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "  3. Open ANOTHER PowerShell window" -ForegroundColor Cyan
Write-Host "  4. Run: npm start" -ForegroundColor Cyan
Write-Host ""
Write-Host "  5. Scan QR code on your phone" -ForegroundColor Cyan
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  GOOD LUCK WITH YOUR PRESENTATION! 🚀" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
