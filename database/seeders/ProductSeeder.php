<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create categories
        $categories = [
            'Saputangan' => Category::firstOrCreate(['slug' => 'saputangan'], ['name' => 'Saputangan']),
            'Pinantupan' => Category::firstOrCreate(['slug' => 'pinantupan'], ['name' => 'Pinantupan']),
            'Birey-Birey' => Category::firstOrCreate(['slug' => 'birey-birey'], ['name' => 'Birey-Birey']),
            'Sinaluan' => Category::firstOrCreate(['slug' => 'sinaluan'], ['name' => 'Sinaluan']),
        ];

        // Products data
        $products = [
            [
                'name' => 'Saputangan',
                'slug' => 'saputangan',
                'description' => 'The Saputangan is a square piece of woven cloth usually measuring no less than standard size with traditional Yakan patterns.',
                'price' => 50.00,
                'category_id' => $categories['Saputangan']->id,
                'image' => 'saputangan.jpg',
                'stock' => 50,
                'status' => 'active',
            ],
            [
                'name' => 'Pinantupan',
                'slug' => 'pinantupan',
                'description' => 'Pinantupan uses simple patterns like flowers and diamonds and are also used for special occasions and traditional celebrations.',
                'price' => 50.00,
                'category_id' => $categories['Pinantupan']->id,
                'image' => 'pinantupan.jpg',
                'stock' => 45,
                'status' => 'active',
            ],
            [
                'name' => 'Birey-Birey',
                'slug' => 'birey-birey',
                'description' => 'Birey-birey is a traditional handwoven textile pattern that resembles the sections of rice fields.',
                'price' => 50.00,
                'category_id' => $categories['Birey-Birey']->id,
                'image' => 'birey.jpg',
                'stock' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'Saputangan Classic',
                'slug' => 'saputangan-classic',
                'description' => 'Classic design with traditional Yakan patterns and vibrant colors, perfect for everyday use.',
                'price' => 60.00,
                'category_id' => $categories['Saputangan']->id,
                'image' => 'saputangan-classic.jpg',
                'stock' => 35,
                'status' => 'active',
            ],
            [
                'name' => 'Sinaluan',
                'slug' => 'sinaluan',
                'description' => 'Sinaluan features intricate geometric patterns representing Yakan heritage and craftsmanship.',
                'price' => 75.00,
                'category_id' => $categories['Sinaluan']->id,
                'image' => 'sinaluan.jpg',
                'stock' => 30,
                'status' => 'active',
            ],
            [
                'name' => 'Pinantupan Premium',
                'slug' => 'pinantupan-premium',
                'description' => 'Premium quality Pinantupan with detailed floral patterns and superior weaving technique.',
                'price' => 85.00,
                'category_id' => $categories['Pinantupan']->id,
                'image' => 'pinantupan-premium.jpg',
                'stock' => 25,
                'status' => 'active',
            ],
            [
                'name' => 'Birey-Birey Deluxe',
                'slug' => 'birey-birey-deluxe',
                'description' => 'Deluxe version of Birey-Birey with enhanced colors and intricate detailing.',
                'price' => 70.00,
                'category_id' => $categories['Birey-Birey']->id,
                'image' => 'birey-deluxe.jpg',
                'stock' => 28,
                'status' => 'active',
            ],
            [
                'name' => 'Sinaluan Premium',
                'slug' => 'sinaluan-premium',
                'description' => 'Premium Sinaluan with extra fine weaving and exclusive color combinations.',
                'price' => 95.00,
                'category_id' => $categories['Sinaluan']->id,
                'image' => 'sinaluan-premium.jpg',
                'stock' => 20,
                'status' => 'active',
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['slug' => $productData['slug']], 
                $productData
            );
        }

        $this->command->info('Products seeded successfully!');
    }
}
