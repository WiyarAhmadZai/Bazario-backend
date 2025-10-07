<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Product;

echo "Testing Admin API...\n";

// Check if we have any admin users
$adminUsers = User::where('role', 'admin')->get();
echo "Admin users found: " . $adminUsers->count() . "\n";

if ($adminUsers->count() > 0) {
    $admin = $adminUsers->first();
    echo "First admin: " . $admin->email . " (ID: " . $admin->id . ")\n";
    echo "Is admin: " . ($admin->isAdmin() ? 'Yes' : 'No') . "\n";
}

// Check products
$products = Product::with('seller')->take(3)->get();
echo "Products found: " . $products->count() . "\n";

foreach ($products as $product) {
    echo "Product ID: " . $product->id . ", Title: " . $product->title . ", Status: " . $product->status . "\n";
    echo "Images: " . $product->images . " (Type: " . gettype($product->images) . ")\n";
    if ($product->seller) {
        echo "Seller: " . $product->seller->email . " (Role: " . $product->seller->role . ")\n";
    }
    echo "---\n";
}
