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

        // Get list of local images
        $localImages = [
            '648491.jpg',
            'IMG.jpg',
            'IMG_3605.JPG',
            'IMG_3612.JPG',
            'IMG_3614.JPG',
            'IMG_3623.JPG',
            'IMG_3624.JPG',
            'IMG_3628.JPG',
            'IMG_3629.JPG',
            'abstract-architecture-background-brick-194096.jpg',
            'abstract-art-circle-clockwork-414579.jpg',
            'abundance-bank-banking-banknotes-259027.jpg',
            'adli-wahid-3-QB-YKxTKY-unsplash.jpg',
            'aerial-view-beach-beautiful-cliff-462162.jpg',
            'apple-business-computer-connection-392018.jpg',
            'apple-computer-decor-design-326502.jpg',
            'apple-office-internet-ipad-38544.jpg',
            'architecture-art-bridge-cliff-459203.jpg',
            'castle-1071188.jpg',
            'cottages-in-the-middle-of-beach-753626.jpg',
            'ddd.jpg',
            'dsgvo-3456746_1920.jpg',
            'earth-5639927_1920.jpg',
            'egypt-3574221_1920.jpg',
            'environmental-protection-326923.jpg',
            'fahrul-azmi-gyKmF0vnfBs-unsplash.jpg',
            'footsteps-3938563.jpg',
            'forest-with-sunlight-158251.jpg',
            'illustration-of-moon-showing-during-sunset-884788.jpg',
            'wp11994780.jpg',
            'wp5419943.jpg'
        ];

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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
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
                'view_count' => rand(10, 100),
            ],
        ];

        // Create products
        foreach ($products as $index => $productData) {
            // Assign seller (rotate through sellers)
            $seller = $sellers[$index % count($sellers)];

            $productData['seller_id'] = $seller->id;
            $productData['slug'] = \Illuminate\Support\Str::slug($productData['title']) . '-' . time() . '-' . ($index + 1);

            // Assign 1-3 random images to each product
            $productImages = [];
            $numImages = rand(1, 3);
            for ($i = 0; $i < $numImages; $i++) {
                $randomImage = $localImages[array_rand($localImages)];
                $productImages[] = 'src/assets/' . $randomImage;
            }
            $productData['images'] = json_encode($productImages);

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
                'slug' => 'sample-product-' . time() . '-' . ($i + 1),
                'description' => 'This is a sample product description for product #' . ($i + 1),
                'price' => rand(10, 500) + (rand(0, 99) / 100),
                'discount' => rand(0, 50) + (rand(0, 99) / 100),
                'stock' => rand(1, 100),
                'category_id' => $category->id,
                'status' => ['approved', 'pending', 'rejected'][array_rand(['approved', 'pending', 'rejected'])],
                'is_featured' => rand(0, 10) > 8, // 20% chance of being featured
                'view_count' => rand(0, 50),
            ];

            // Assign 1-3 random images to each product
            $productImages = [];
            $numImages = rand(1, 3);
            for ($j = 0; $j < $numImages; $j++) {
                $randomImage = $localImages[array_rand($localImages)];
                $productImages[] = 'src/assets/' . $randomImage;
            }
            $productData['images'] = json_encode($productImages);

            Product::create($productData);
        }
    }
}
