<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate a login attempt
$email = 'admin@yakan.com';
$password = 'admin123';

echo "=== Admin Login Test ===\n";

// Check if user exists
$user = \App\Models\User::where('email', $email)->first();
if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "User found: {$user->email}, Role: {$user->role}\n";

// Test password hash
echo "Testing password: " . ($user && \Hash::check($password, $user->password) ? "✓ CORRECT" : "✗ WRONG") . "\n";

// Check auth config
echo "\n=== Auth Configuration ===\n";
$authConfig = config('auth');
echo "Admin guard provider: " . $authConfig['guards']['admin']['provider'] . "\n";
echo "Admin provider model: " . $authConfig['providers']['users']['model'] . "\n";

// Test auth attempt directly
echo "\n=== Testing Auth::guard('admin')->attempt() ===\n";
$credentials = ['email' => $email, 'password' => $password];
$result = \Illuminate\Support\Facades\Auth::guard('admin')->attempt($credentials);
echo "Login attempt result: " . ($result ? "✓ SUCCESS" : "✗ FAILED") . "\n";

if ($result) {
    echo "User authenticated: " . \Illuminate\Support\Facades\Auth::guard('admin')->user()->email . "\n";
    echo "Session ID: " . session()->getId() . "\n";
    echo "Session data: " . json_encode(session()->all(), JSON_PRETTY_PRINT) . "\n";
}
