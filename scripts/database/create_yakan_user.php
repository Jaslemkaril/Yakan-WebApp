<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'first_name' => 'Yakan',
    'last_name' => 'User',
    'name' => 'Yakan User',
    'email' => 'user@yakan.com',
    'password' => Hash::make('password'),
    'role' => 'user',
    'email_verified_at' => now(),
]);

echo "User created successfully!\n";
echo "Email: user@yakan.com\n";
echo "Password: password\n";
