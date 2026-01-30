<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create admin user
$admin = User::firstOrCreate(
    ['email' => 'admin@yakan.com'],
    [
        'name' => 'Admin User',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]
);

echo "✓ Admin user created/verified:\n";
echo "  Email: {$admin->email}\n";
echo "  Role: {$admin->role}\n";
echo "  ID: {$admin->id}\n";
