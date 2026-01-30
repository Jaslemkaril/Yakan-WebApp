<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate a browser request with session
session_start();

$login = 'admin@yakan.com';
$pass = 'admin123';

echo "=== Simulating Admin Login Flow ===\n\n";

// Step 1: Attempt login
echo "Step 1: Attempting login...\n";
$result = \Auth::guard('admin')->attempt(['email' => $login, 'password' => $pass]);
echo "Login result: " . ($result ? "✓ SUCCESS" : "✗ FAILED") . "\n";

if ($result) {
    echo "User ID in guard: " . \Auth::guard('admin')->id() . "\n";
    echo "Session ID: " . session()->getId() . "\n";
    echo "Session data stored: " . json_encode(session()->all()) . "\n";
    
    // Step 2: Check if admin guard still has the user
    echo "\nStep 2: Checking if admin guard persists...\n";
    echo "Auth::guard('admin')->check(): " . (\Auth::guard('admin')->check() ? "✓ TRUE" : "✗ FALSE") . "\n";
    echo "Auth::guard('admin')->id(): " . \Auth::guard('admin')->id() . "\n";
    
    // Step 3: Simulate a second request (new bootstrap)
    echo "\nStep 3: Simulating a second request (new bootstrap)...\n";
    $app2 = require 'bootstrap/app.php';
    $app2->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "Auth::guard('admin')->check() on new request: " . (\Auth::guard('admin')->check() ? "✓ TRUE" : "✗ FALSE") . "\n";
    echo "Auth::guard('admin')->id() on new request: " . \Auth::guard('admin')->id() . "\n";
}
