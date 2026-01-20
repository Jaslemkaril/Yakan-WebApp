<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Setup Routes - For Initial Deployment
|--------------------------------------------------------------------------
| Visit these routes in your browser to set up your application
| IMPORTANT: Remove or disable these routes after initial setup!
*/

Route::get('/setup/create-admin', function () {
    // Check if admin already exists
    $existingAdmin = User::where('email', 'admin@yakan.com')->first();
    
    if ($existingAdmin) {
        return response()->json([
            'status' => 'exists',
            'message' => 'Admin user already exists!',
            'credentials' => [
                'email' => 'admin@yakan.com',
                'password' => 'Use your existing password',
            ]
        ]);
    }
    
    // Create admin user
    $admin = User::create([
        'name' => 'Admin User',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@yakan.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Admin user created successfully!',
        'credentials' => [
            'email' => 'admin@yakan.com',
            'password' => 'admin123',
            'note' => 'Please change this password after first login!'
        ],
        'login_url' => url('/admin/login')
    ]);
});

Route::get('/setup/create-test-data', function () {
    // Create some test categories
    $categories = [
        ['name' => 'Traditional Patterns', 'slug' => 'traditional-patterns', 'description' => 'Authentic Yakan traditional patterns'],
        ['name' => 'Modern Designs', 'slug' => 'modern-designs', 'description' => 'Contemporary Yakan-inspired designs'],
        ['name' => 'Custom Orders', 'slug' => 'custom-orders', 'description' => 'Custom made Yakan textiles'],
    ];

    foreach ($categories as $category) {
        \App\Models\Category::firstOrCreate(
            ['slug' => $category['slug']],
            $category
        );
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Test data created successfully!',
        'categories_created' => count($categories)
    ]);
});

Route::get('/setup/info', function () {
    return response()->json([
        'app' => 'Yakan E-commerce Setup',
        'available_routes' => [
            'create_admin' => url('/setup/create-admin'),
            'create_test_data' => url('/setup/create-test-data'),
        ],
        'database' => [
            'connection' => config('database.default'),
            'users_count' => User::count(),
            'admins_count' => User::where('role', 'admin')->count(),
        ]
    ]);
});
