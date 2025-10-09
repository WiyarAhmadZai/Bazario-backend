<?php

// Test API product update endpoint
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing API Product Update Endpoint ===\n";

try {
    // Get a test product and user
    $product = Product::first();
    $user = User::where('role', 'seller')->first();

    if (!$product) {
        echo "No products found in database.\n";
        exit(1);
    }

    if (!$user) {
        echo "No seller users found in database.\n";
        exit(1);
    }

    echo "Testing with product ID: {$product->id}\n";
    echo "Testing with user ID: {$user->id}\n";
    echo "Original title: {$product->title}\n";
    echo "Original price: {$product->price}\n";

    // Simulate API request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'title' => $product->title . ' - API UPDATED',
        'price' => $product->price + 2.00,
        'description' => $product->description . ' - Updated via API'
    ]);

    // Set authenticated user
    auth()->login($user);

    echo "\nTesting SellerController::updateProduct...\n";

    // Test SellerController update
    $sellerController = new \App\Http\Controllers\SellerController();
    $response = $sellerController->updateProduct($request, $product->id);

    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response data: " . $response->getContent() . "\n";

    // Check if update worked
    $product->refresh();
    echo "\nAfter API update:\n";
    echo "Title: {$product->title}\n";
    echo "Price: {$product->price}\n";

    if (str_contains($product->title, 'API UPDATED')) {
        echo "\n✅ API UPDATE SUCCESSFUL!\n";
    } else {
        echo "\n❌ API UPDATE FAILED!\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== API Test Complete ===\n";
