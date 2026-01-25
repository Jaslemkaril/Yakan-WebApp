<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeederUpdated extends Seeder
{
    public function run()
    {
        // Create admin user with requested email
        User::firstOrCreate(
            ['email' => 'kariljaslem@gmail.com'],
            [
                'name' => 'Karil Jaslim',
                'first_name' => 'Karil',
                'last_name' => 'Jaslim',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create additional admin user
        User::firstOrCreate(
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

        // Create a regular test user
        User::firstOrCreate(
            ['email' => 'user@yakan.com'],
            [
                'name' => 'Test User',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin users created successfully!');
        $this->command->info('Admin Login: kariljaslem@gmail.com / admin123');
        $this->command->info('Admin Login: admin@yakan.com / admin123');
        $this->command->info('User Login: user@yakan.com / user123');
    }
}
