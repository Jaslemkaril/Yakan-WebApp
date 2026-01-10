<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FabricType;

class FabricTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fabrics = [
            [
                'name' => 'Cotton',
                'icon' => '🌾',
                'description' => 'Soft, breathable, and comfortable for everyday wear',
                'base_price_per_meter' => 250.00,
                'is_active' => true,
            ],
            [
                'name' => 'Silk',
                'icon' => '✨',
                'description' => 'Luxurious, smooth, and perfect for special occasions',
                'base_price_per_meter' => 500.00,
                'is_active' => true,
            ],
            [
                'name' => 'Linen',
                'icon' => '📋',
                'description' => 'Lightweight, durable, and great for warm weather',
                'base_price_per_meter' => 300.00,
                'is_active' => true,
            ],
            [
                'name' => 'Canvas',
                'icon' => '🎒',
                'description' => 'Heavy-duty fabric ideal for bags and durable items',
                'base_price_per_meter' => 350.00,
                'is_active' => true,
            ],
            [
                'name' => 'Chiffon',
                'icon' => '🌫️',
                'description' => 'Lightweight, sheer fabric perfect for overlays and delicate garments',
                'base_price_per_meter' => 400.00,
                'is_active' => true,
            ],
            [
                'name' => 'Jersey Knit',
                'icon' => '👕',
                'description' => 'Stretchy and comfortable knit fabric perfect for t-shirts',
                'base_price_per_meter' => 280.00,
                'is_active' => true,
            ],
            [
                'name' => 'Velvet',
                'icon' => '👔',
                'description' => 'Luxurious fabric with soft pile, ideal for formal wear',
                'base_price_per_meter' => 450.00,
                'is_active' => true,
            ],
            [
                'name' => 'Polyester Blend',
                'icon' => '🧵',
                'description' => 'Durable and wrinkle-resistant blend for everyday wear',
                'base_price_per_meter' => 220.00,
                'is_active' => true,
            ],
        ];

        // Clear existing data to avoid duplicates
        FabricType::query()->delete();

        foreach ($fabrics as $fabric) {
            FabricType::create($fabric);
        }

        $this->command->info('Fabric types seeded successfully!');
    }
}

