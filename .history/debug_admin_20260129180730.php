<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$db = $app->make('db');

// Check admin user
echo "=== Checking Admin User ===\n";
$admin = $db->table('users')->where('email', 'admin@yakan.com')->first();

if ($admin) {
    echo "Admin found:\n";
    echo "  Email: {$admin->email}\n";
    echo "  Role: {$admin->role}\n";
    echo "  ID: {$admin->id}\n";
} else {
    echo "Admin user not found with email admin@yakan.com\n";
    echo "\n=== All users in database ===\n";
    $users = $db->table('users')->get();
    foreach ($users as $user) {
        echo "  - {$user->email} (Role: {$user->role})\n";
    }
}

echo "\n=== Session Configuration ===\n";
echo "SESSION_DRIVER: " . env('SESSION_DRIVER', 'file') . "\n";
echo "SESSION_LIFETIME: " . env('SESSION_LIFETIME', '120') . " minutes\n";
