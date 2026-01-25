<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Creating New Regular User ===\n\n";

try {
    $user = \App\Models\User::create([
        'name' => 'Test Customer',
        'email' => 'customer@yakan.com',
        'password' => bcrypt('customer123')
    ]);
    
    echo "Regular user created successfully!\n";
    echo "Email: customer@yakan.com\n";
    echo "Password: customer123\n";
    echo "User ID: " . $user->id . "\n";
    echo "\nYou can use this account to login as a regular user.\n";
    
} catch (\Exception $e) {
    echo "Error creating user: " . $e->getMessage() . "\n";
}

echo "\n=== Complete ===\n";
