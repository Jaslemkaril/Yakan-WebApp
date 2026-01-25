<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed if database is empty
        if (User::count() === 0) {
            $this->call([
                AdminUserSeeder::class,
                AdminUserSeederUpdated::class,
            ]);
        }

        if (Product::count() === 0) {
            $this->call([
                CategorySeeder::class,
                ProductSeeder::class,
            ]);
        }

        // Always seed these if not present
        $this->call([
            YakanPatternSeeder::class,
            PatternSeeder::class,
            FabricTypeSeeder::class,
            IntendedUseSeeder::class,
        ]);
    }
}
