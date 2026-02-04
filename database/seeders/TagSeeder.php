<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'new',
            'sale',
            'featured',
            'bestseller',
            'popular',
            'trending',
            'limited',
            'exclusive',
            'premium',
            'budget',
            'eco-friendly',
            'handmade',
            'imported',
            'local',
            'organic',
            'recycled',
            'vegan',
            'gluten-free',
            'warranty',
            'gift',
        ];

        foreach ($tags as $tagName) {
            Tag::create(['name' => $tagName]);
        }
    }
}
