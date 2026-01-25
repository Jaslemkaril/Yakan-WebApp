<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking and Creating User ===\n\n";

$users = \App\Models\User::all();

if ($users->isEmpty()) {
    echo "No users found. Creating a new user...\n";
    
    try {
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'user@yakan.com',
            'password' => bcrypt('password')
        ]);
        
        echo "User created successfully!\n";
        echo "Email: user@yakan.com\n";
        echo "Password: password\n";
        echo "User ID: " . $user->id . "\n";
        
    } catch (\Exception $e) {
        echo "Error creating user: " . $e->getMessage() . "\n";
    }
} else {
    echo "Found existing users:\n";
    foreach ($users as $user) {
        echo "ID: " . $user->id . " - Email: " . $user->email . "\n";
    }
    
    echo "\nYou can use any of these existing users, or I can create a new one.\n";
    echo "Would you like me to create a new user anyway? (y/n)\n";
}

echo "\n=== Complete ===\n";
