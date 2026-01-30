<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

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
    echo "Session data stored:\n";
    foreach (session()->all() as $k => $v) {
        echo "  $k => " . (is_array($v) ? json_encode($v) : $v) . "\n";
    }
    
    // Step 2: Check if admin guard still has the user
    echo "\nStep 2: Checking if admin guard persists...\n";
    echo "Auth::guard('admin')->check(): " . (\Auth::guard('admin')->check() ? "✓ TRUE" : "✗ FALSE") . "\n";
    echo "Auth::guard('admin')->id(): " . \Auth::guard('admin')->id() . "\n";
    
    // Step 3: Check what's in the database sessions table
    echo "\nStep 3: Checking database sessions table...\n";
    $sessions = \DB::table('sessions')->where('user_id', \Auth::guard('admin')->id())->get();
    echo "Sessions in DB for user " . \Auth::guard('admin')->id() . ": " . count($sessions) . "\n";
}
