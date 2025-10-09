<?php

// Test the fixed update functionality
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Fixed Product Update ===\n";

try {
    // Get a test product
    $product = Product::first();
    if (!$product) {
        echo "No products found!\n";
        exit(1);
    }

    // Get a seller user
    $seller = User::where('id', '>', 1)->first();
    if (!$seller) {
        echo "No seller users found!\n";
        exit(1);
    }

    echo "Testing with product ID: {$product->id}\n";
    echo "Testing with seller ID: {$seller->id}\n";
    echo "Original title: {$product->title}\n";
    echo "Original price: {$product->price}\n";

    // Ensure product belongs to seller
    if ($product->seller_id != $seller->id) {
        $product->seller_id = $seller->id;
        $product->save();
        echo "✅ Updated product seller_id to {$seller->id}\n";
    }

    // Test the update
    $request = new Request();
    $request->merge([
        'title' => $product->title . ' - FIXED TEST',
        'price' => $product->price + 3.00,
        'description' => $product->description . ' - Fixed test'
    ]);

    auth()->login($seller);

    $sellerController = new \App\Http\Controllers\SellerController();
    $response = $sellerController->updateProduct($request, $product->id);

    echo "Response status: " . $response->getStatusCode() . "\n";

    // Check if update worked
    $product->refresh();
    echo "\nAfter update:\n";
    echo "Title: {$product->title}\n";
    echo "Price: {$product->price}\n";

    if (str_contains($product->title, 'FIXED TEST')) {
        echo "✅ UPDATE IS NOW WORKING!\n";

        // Restore original
        $product->title = str_replace(' - FIXED TEST', '', $product->title);
        $product->price = $product->price - 3.00;
        $product->save();
        echo "✅ Original values restored.\n";
    } else {
        echo "❌ UPDATE STILL NOT WORKING!\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
