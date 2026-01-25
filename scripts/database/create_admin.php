<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'name' => 'Admin User',
    'email' => 'admin@yakan.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);

echo "Admin account created!\n";
echo "Email: admin@yakan.com\n";
echo "Password: admin123\n";
