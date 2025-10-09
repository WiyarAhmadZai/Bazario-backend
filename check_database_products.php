<?php

// Check database products and shop page issue
require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\User;
use App\Models\Category;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Products Check ===\n";

try {
    // Check total products
    $totalProducts = Product::count();
    echo "Total products in database: {$totalProducts}\n\n";

    if ($totalProducts > 0) {
        echo "Product Details:\n";
        echo "ID | Title | Status | Seller ID | Price | Created\n";
        echo "---|-------|-------|-----------|-------|--------\n";

        foreach (Product::all() as $product) {
            echo "{$product->id} | {$product->title} | {$product->status} | {$product->seller_id} | {$product->price} | {$product->created_at}\n";
        }

        // Check approved products (should show in shop)
        $approvedProducts = Product::where('status', 'approved')->count();
        echo "\nApproved products (should show in shop): {$approvedProducts}\n";

        // Check pending products
        $pendingProducts = Product::where('status', 'pending')->count();
        echo "Pending products: {$pendingProducts}\n";

        // Check users
        $totalUsers = User::count();
        echo "\nTotal users: {$totalUsers}\n";

        // Check categories
        $totalCategories = Category::count();
        echo "Total categories: {$totalCategories}\n";

        // Test shop page query
        echo "\n=== Testing Shop Page Query ===\n";
        $shopProducts = Product::with('category', 'seller')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        echo "Products that should show in shop: " . $shopProducts->count() . "\n";

        if ($shopProducts->count() > 0) {
            echo "Shop products:\n";
            foreach ($shopProducts as $product) {
                $sellerName = $product->seller ? $product->seller->name : 'Unknown';
                echo "- {$product->title} (Status: {$product->status}, Seller: {$sellerName})\n";
            }
        } else {
            echo "❌ NO PRODUCTS WILL SHOW IN SHOP!\n";
            echo "This is why the shop page is empty.\n";
        }
    } else {
        echo "❌ NO PRODUCTS IN DATABASE!\n";
        echo "This explains both issues:\n";
        echo "1. No products to update\n";
        echo "2. No products to show in shop\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
