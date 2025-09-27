<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
            ],
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Add subcategories
        $subcategories = [
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                'parent_id' => Category::where('slug', 'electronics')->first()->id,
            ],
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'parent_id' => Category::where('slug', 'electronics')->first()->id,
            ],
            [
                'name' => 'Men\'s Clothing',
                'slug' => 'mens-clothing',
                'parent_id' => Category::where('slug', 'fashion')->first()->id,
            ],
            [
                'name' => 'Women\'s Clothing',
                'slug' => 'womens-clothing',
                'parent_id' => Category::where('slug', 'fashion')->first()->id,
            ],
        ];

        foreach ($subcategories as $subcategory) {
            Category::firstOrCreate(
                ['slug' => $subcategory['slug']],
                $subcategory
            );
        }
    }
}
