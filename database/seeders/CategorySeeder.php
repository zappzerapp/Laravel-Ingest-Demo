<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Clothing',
            'Food & Beverages',
            'Home & Garden',
            'Sports & Outdoors',
            'Books & Media',
            'Health & Beauty',
            'Toys & Games',
            'Automotive',
            'Office Supplies',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
