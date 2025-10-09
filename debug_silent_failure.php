<?php

// Debug: API returns success but database doesn't update
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING: API Success but Database Not Updated ===\n";

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

    // Enable query logging
    DB::enableQueryLog();

    // Test the exact scenario: API success but no database update
    echo "\n=== Testing SellerController Update ===\n";

    $request = new Request();
    $request->merge([
        'title' => $product->title . ' - DEBUG TEST',
        'price' => $product->price + 1.00,
        'description' => $product->description . ' - Debug test'
    ]);

    // Set authenticated user
    auth()->login($seller);

    // Check if product belongs to seller
    echo "Product seller_id: {$product->seller_id}\n";
    echo "Current user_id: {$seller->id}\n";

    if ($product->seller_id != $seller->id) {
        echo "❌ PRODUCT DOESN'T BELONG TO SELLER!\n";
        echo "This will cause the update to fail silently.\n";

        // Update product to belong to current seller for testing
        $product->seller_id = $seller->id;
        $product->save();
        echo "✅ Updated product seller_id to {$seller->id} for testing\n";
    }

    $sellerController = new \App\Http\Controllers\SellerController();

    // Capture the response
    $response = $sellerController->updateProduct($request, $product->id);

    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response data: " . $response->getContent() . "\n";

    // Check queries executed
    $queries = DB::getQueryLog();
    echo "\n=== Database Queries Executed ===\n";
    foreach ($queries as $query) {
        echo "SQL: " . $query['query'] . "\n";
        echo "Bindings: " . json_encode($query['bindings']) . "\n";
        echo "Time: " . $query['time'] . "ms\n\n";
    }

    // Check if update actually worked
    $product->refresh();
    echo "\n=== After Update Check ===\n";
    echo "Title: {$product->title}\n";
    echo "Price: {$product->price}\n";

    if (str_contains($product->title, 'DEBUG TEST')) {
        echo "✅ DATABASE UPDATE SUCCESSFUL!\n";
    } else {
        echo "❌ DATABASE UPDATE FAILED!\n";
        echo "API returned success but database wasn't updated.\n";

        // Check for potential issues
        echo "\n=== Debugging Potential Issues ===\n";

        // Check if product exists
        $productExists = Product::find($product->id);
        echo "Product exists: " . ($productExists ? 'YES' : 'NO') . "\n";

        // Check seller ownership
        $ownershipCheck = Product::where('id', $product->id)->where('seller_id', $seller->id)->exists();
        echo "Seller ownership: " . ($ownershipCheck ? 'YES' : 'NO') . "\n";

        // Check if validation passed
        echo "Request data: " . json_encode($request->all()) . "\n";

        // Try manual update
        echo "\n=== Trying Manual Update ===\n";
        $manualUpdate = $product->update([
            'title' => $product->title . ' - MANUAL',
            'price' => $product->price + 0.50
        ]);
        echo "Manual update result: " . ($manualUpdate ? 'SUCCESS' : 'FAILED') . "\n";

        $product->refresh();
        echo "After manual update - Title: {$product->title}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
