<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Saputangan', 'slug' => 'saputangan'],
            ['name' => 'Pinantupan', 'slug' => 'pinantupan'],
            ['name' => 'Birey-Birey', 'slug' => 'birey-birey'],
            ['name' => 'Sinaluan', 'slug' => 'sinaluan'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
