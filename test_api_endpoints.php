<?php

// Test API endpoints for product update
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing API Product Update Endpoints ===\n";

try {
    // Get a test product
    $product = Product::first();
    if (!$product) {
        echo "No products found!\n";
        exit(1);
    }

    // Get a seller user (users with empty role are sellers based on seeder)
    $seller = User::where('id', '>', 1)->first(); // Skip admin user
    if (!$seller) {
        echo "No seller users found!\n";
        exit(1);
    }

    echo "Testing with product ID: {$product->id}\n";
    echo "Testing with seller ID: {$seller->id}\n";
    echo "Original title: {$product->title}\n";

    // Test SellerController update
    echo "\n=== Testing SellerController::updateProduct ===\n";

    $request = new Request();
    $request->merge([
        'title' => $product->title . ' - SELLER UPDATE',
        'price' => $product->price + 5.00,
        'description' => $product->description . ' - Updated by seller'
    ]);

    // Set authenticated user
    auth()->login($seller);

    $sellerController = new \App\Http\Controllers\SellerController();
    $response = $sellerController->updateProduct($request, $product->id);

    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response data: " . $response->getContent() . "\n";

    // Check if update worked
    $product->refresh();
    echo "After seller update - Title: {$product->title}\n";

    if (str_contains($product->title, 'SELLER UPDATE')) {
        echo "✅ SELLER UPDATE SUCCESSFUL!\n";

        // Restore original
        $product->title = str_replace(' - SELLER UPDATE', '', $product->title);
        $product->price = $product->price - 5.00;
        $product->save();
        echo "✅ Original values restored.\n";
    } else {
        echo "❌ SELLER UPDATE FAILED!\n";
    }

    // Test AdminController update
    echo "\n=== Testing AdminController::updateProduct ===\n";

    $admin = User::where('role', 'admin')->first();
    if ($admin) {
        auth()->login($admin);

        $request2 = new Request();
        $request2->merge([
            'title' => $product->title . ' - ADMIN UPDATE',
            'price' => $product->price + 10.00,
            'status' => 'approved'
        ]);

        $adminController = new \App\Http\Controllers\AdminController();
        $response2 = $adminController->updateProduct($request2, $product->id);

        echo "Response status: " . $response2->getStatusCode() . "\n";
        echo "Response data: " . $response2->getContent() . "\n";

        // Check if update worked
        $product->refresh();
        echo "After admin update - Title: {$product->title}\n";

        if (str_contains($product->title, 'ADMIN UPDATE')) {
            echo "✅ ADMIN UPDATE SUCCESSFUL!\n";
        } else {
            echo "❌ ADMIN UPDATE FAILED!\n";
        }
    } else {
        echo "No admin user found for testing.\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== API Endpoint Test Complete ===\n";
