<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Creating Regular User ===\n\n";

try {
    $user = \App\Models\User::create([
        'name' => 'Regular User',
        'email' => 'user@yakan.com',
        'password' => bcrypt('user123')
    ]);
    
    echo "Regular user created successfully!\n";
    echo "Email: user@yakan.com\n";
    echo "Password: user123\n";
    echo "User ID: " . $user->id . "\n";
    echo "\nYou can use this account to login as a regular user.\n";
    
} catch (\Exception $e) {
    echo "Error creating user: " . $e->getMessage() . "\n";
    echo "The email might already exist.\n";
}

echo "\n=== Complete ===\n";
