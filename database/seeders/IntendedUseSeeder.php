<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IntendedUse;

class IntendedUseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $intendedUses = [
            [
                'name' => 'Clothing',
                'description' => 'For making garments and apparel',
                'is_active' => true,
            ],
            [
                'name' => 'Home Decor',
                'description' => 'For home furnishings and decorative items',
                'is_active' => true,
            ],
            [
                'name' => 'Crafts',
                'description' => 'For DIY projects and craft materials',
                'is_active' => true,
            ],
            [
                'name' => 'Upholstery',
                'description' => 'For furniture upholstery and cushions',
                'is_active' => true,
            ],
            [
                'name' => 'Accessories',
                'description' => 'For bags, scarves, and fashion accessories',
                'is_active' => true,
            ],
            [
                'name' => 'Bedding',
                'description' => 'For bed linens and bedding materials',
                'is_active' => true,
            ],
        ];

        // Clear existing data to avoid duplicates
        IntendedUse::query()->delete();

        foreach ($intendedUses as $use) {
            IntendedUse::create($use);
        }

        $this->command->info('Intended uses seeded successfully!');
    }
}
