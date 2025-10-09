<?php

// Create test product and test update functionality
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Creating Test Product and Testing Update ===\n";

try {
    // Get a seller user
    $seller = User::where('id', '>', 1)->first();
    if (!$seller) {
        echo "No seller users found!\n";
        exit(1);
    }

    // Get a category
    $category = Category::first();
    if (!$category) {
        echo "No categories found!\n";
        exit(1);
    }

    echo "Using seller: {$seller->name} (ID: {$seller->id})\n";
    echo "Using category: {$category->name} (ID: {$category->id})\n";

    // Create a test product
    $testProduct = Product::create([
        'seller_id' => $seller->id,
        'title' => 'Test Product for Update',
        'slug' => 'test-product-for-update',
        'description' => 'This is a test product to verify update functionality',
        'price' => 99.99,
        'discount' => 0.00,
        'stock' => 10,
        'category_id' => $category->id,
        'status' => 'approved',
        'is_featured' => false,
        'view_count' => 0,
        'images' => json_encode([])
    ]);

    echo "\n✅ Test product created successfully!\n";
    echo "Product ID: {$testProduct->id}\n";
    echo "Product Title: {$testProduct->title}\n";
    echo "Product Price: {$testProduct->price}\n";

    // Now test the update functionality
    echo "\n=== Testing Product Update ===\n";

    $request = new Request();
    $request->merge([
        'title' => 'Test Product - UPDATED',
        'price' => 149.99,
        'description' => 'This product has been updated successfully!',
        'stock' => 20
    ]);

    // Set authenticated user
    auth()->login($seller);

    $sellerController = new \App\Http\Controllers\SellerController();
    $response = $sellerController->updateProduct($request, $testProduct->id);

    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response data: " . $response->getContent() . "\n";

    // Check if update worked
    $testProduct->refresh();
    echo "\n=== After Update Check ===\n";
    echo "Title: {$testProduct->title}\n";
    echo "Price: {$testProduct->price}\n";
    echo "Description: {$testProduct->description}\n";
    echo "Stock: {$testProduct->stock}\n";

    if (str_contains($testProduct->title, 'UPDATED') && $testProduct->price == 149.99) {
        echo "\n✅ PRODUCT UPDATE IS WORKING PERFECTLY!\n";
        echo "The database is being updated correctly.\n";
    } else {
        echo "\n❌ PRODUCT UPDATE IS STILL NOT WORKING!\n";
        echo "Title contains 'UPDATED': " . (str_contains($testProduct->title, 'UPDATED') ? 'YES' : 'NO') . "\n";
        echo "Price is 149.99: " . ($testProduct->price == 149.99 ? 'YES' : 'NO') . "\n";
    }

    // Test with different values
    echo "\n=== Testing Second Update ===\n";

    $request2 = new Request();
    $request2->merge([
        'title' => 'Test Product - SECOND UPDATE',
        'price' => 199.99,
        'description' => 'This is the second update test!'
    ]);

    $response2 = $sellerController->updateProduct($request2, $testProduct->id);
    echo "Second update response status: " . $response2->getStatusCode() . "\n";

    $testProduct->refresh();
    echo "After second update:\n";
    echo "Title: {$testProduct->title}\n";
    echo "Price: {$testProduct->price}\n";

    if (str_contains($testProduct->title, 'SECOND UPDATE') && $testProduct->price == 199.99) {
        echo "\n✅ SECOND UPDATE ALSO WORKING!\n";
    } else {
        echo "\n❌ SECOND UPDATE FAILED!\n";
    }

    // Clean up - delete test product
    $testProduct->delete();
    echo "\n✅ Test product cleaned up.\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
