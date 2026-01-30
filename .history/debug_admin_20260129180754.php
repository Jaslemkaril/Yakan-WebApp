<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check admin user
echo "=== Checking Admin User ===\n";
$admin = \DB::table('users')->where('email', 'admin@yakan.com')->first();

if ($admin) {
    echo "Admin found:\n";
    echo "  Email: {$admin->email}\n";
    echo "  Role: {$admin->role}\n";
    echo "  ID: {$admin->id}\n";
} else {
    echo "Admin user not found with email admin@yakan.com\n";
    echo "\n=== All users in database ===\n";
    $users = DB::table('users')->get();
    foreach ($users as $user) {
        echo "  - {$user->email} (Role: {$user->role})\n";
    }
}

echo "\n=== Session Configuration ===\n";
echo "SESSION_DRIVER: " . env('SESSION_DRIVER', 'file') . "\n";
echo "SESSION_LIFETIME: " . env('SESSION_LIFETIME', '120') . " minutes\n";
