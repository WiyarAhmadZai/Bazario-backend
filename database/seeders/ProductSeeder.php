<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sellers
        $sellers = User::role('seller')->get();
        $categories = Category::all();

        // Product data
        $products = [
            [
                'title' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced camera system and A17 Pro chip',
                'price' => 999.99,
                'discount' => 0.00,
                'stock' => 50,
                'category_id' => $categories->where('slug', 'smartphones')->first()->id,
                'status' => 'approved',
                'is_featured' => true,
            ],
            [
                'title' => 'Samsung Galaxy S24',
                'description' => 'Flagship Android smartphone with powerful performance',
                'price' => 899.99,
                'discount' => 50.00,
                'stock' => 30,
                'category_id' => $categories->where('slug', 'smartphones')->first()->id,
                'status' => 'approved',
                'is_featured' => true,
            ],
            [
                'title' => 'MacBook Pro 16"',
                'description' => 'Professional laptop with M3 Pro chip and stunning display',
                'price' => 2499.99,
                'discount' => 0.00,
                'stock' => 20,
                'category_id' => $categories->where('slug', 'laptops')->first()->id,
                'status' => 'approved',
                'is_featured' => true,
            ],
            [
                'title' => 'Dell XPS 13',
                'description' => 'Premium ultrabook with Intel Core processor',
                'price' => 1299.99,
                'discount' => 100.00,
                'stock' => 25,
                'category_id' => $categories->where('slug', 'laptops')->first()->id,
                'status' => 'approved',
                'is_featured' => false,
            ],
            [
                'title' => 'Men\'s Leather Jacket',
                'description' => 'Genuine leather jacket for men, perfect for winter',
                'price' => 199.99,
                'discount' => 20.00,
                'stock' => 40,
                'category_id' => $categories->where('slug', 'mens-clothing')->first()->id,
                'status' => 'approved',
                'is_featured' => true,
            ],
            [
                'title' => 'Women\'s Summer Dress',
                'description' => 'Lightweight summer dress for women, floral pattern',
                'price' => 79.99,
                'discount' => 0.00,
                'stock' => 100,
                'category_id' => $categories->where('slug', 'womens-clothing')->first()->id,
                'status' => 'approved',
                'is_featured' => false,
            ],
            [
                'title' => '4K Smart TV 55"',
                'description' => 'Ultra HD smart TV with HDR and voice control',
                'price' => 699.99,
                'discount' => 50.00,
                'stock' => 15,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'status' => 'approved',
                'is_featured' => true,
            ],
            [
                'title' => 'Wireless Headphones',
                'description' => 'Noise-cancelling wireless headphones with 30hr battery',
                'price' => 299.99,
                'discount' => 30.00,
                'stock' => 75,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'status' => 'approved',
                'is_featured' => false,
            ],
            [
                'title' => 'Coffee Maker',
                'description' => 'Programmable coffee maker with thermal carafe',
                'price' => 89.99,
                'discount' => 10.00,
                'stock' => 60,
                'category_id' => $categories->where('slug', 'home-garden')->first()->id,
                'status' => 'approved',
                'is_featured' => false,
            ],
            [
                'title' => 'Yoga Mat',
                'description' => 'Eco-friendly non-slip yoga mat with carrying strap',
                'price' => 29.99,
                'discount' => 0.00,
                'stock' => 200,
                'category_id' => $categories->where('slug', 'sports-outdoors')->first()->id,
                'status' => 'approved',
                'is_featured' => false,
            ],
            // Pending products
            [
                'title' => 'Smart Watch Series 5',
                'description' => 'Advanced smartwatch with health monitoring features',
                'price' => 399.99,
                'discount' => 0.00,
                'stock' => 35,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'status' => 'pending',
                'is_featured' => false,
            ],
            [
                'title' => 'Bluetooth Speaker',
                'description' => 'Portable waterproof Bluetooth speaker with 20W sound',
                'price' => 79.99,
                'discount' => 10.00,
                'stock' => 80,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'status' => 'pending',
                'is_featured' => false,
            ],
        ];

        // Create products
        foreach ($products as $index => $productData) {
            // Assign seller (rotate through sellers)
            $seller = $sellers[$index % count($sellers)];

            $productData['seller_id'] = $seller->id;
            $productData['slug'] = \Illuminate\Support\Str::slug($productData['title']) . '-' . ($index + 1);
            $productData['images'] = json_encode([
                'https://via.placeholder.com/300x300.png?text=Product+Image+' . ($index + 1)
            ]);

            Product::create($productData);
        }

        // Create additional products to reach 50 total
        $additionalProducts = 40; // 50 total - 10 existing
        for ($i = 0; $i < $additionalProducts; $i++) {
            $seller = $sellers[array_rand($sellers->toArray())];
            $category = $categories->random();

            $productData = [
                'seller_id' => $seller->id,
                'title' => 'Sample Product ' . ($i + 1),
                'slug' => 'sample-product-' . ($i + 1),
                'description' => 'This is a sample product description for product #' . ($i + 1),
                'price' => rand(10, 500) + (rand(0, 99) / 100),
                'discount' => rand(0, 50) + (rand(0, 99) / 100),
                'stock' => rand(1, 100),
                'category_id' => $category->id,
                'status' => ['approved', 'pending', 'rejected'][array_rand(['approved', 'pending', 'rejected'])],
                'is_featured' => rand(0, 10) > 8, // 20% chance of being featured
                'images' => json_encode([
                    'https://via.placeholder.com/300x300.png?text=Sample+Product+' . ($i + 1)
                ]),
            ];

            Product::create($productData);
        }
    }
}
