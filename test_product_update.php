<?php

// Test product update functionality
require_once 'vendor/autoload.php';

use App\Models\Product;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Product Update Functionality ===\n";

try {
    // Get a test product
    $product = Product::first();

    if (!$product) {
        echo "No products found in database.\n";
        exit(1);
    }

    echo "Testing with product ID: {$product->id}\n";
    echo "Original title: {$product->title}\n";
    echo "Original price: {$product->price}\n";

    // Test update
    $originalTitle = $product->title;
    $originalPrice = $product->price;

    $newTitle = $originalTitle . ' - UPDATED';
    $newPrice = $originalPrice + 1.00;

    echo "\nUpdating product...\n";
    echo "New title: {$newTitle}\n";
    echo "New price: {$newPrice}\n";

    // Perform update
    $updateResult = $product->update([
        'title' => $newTitle,
        'price' => $newPrice
    ]);

    echo "Update result: " . ($updateResult ? 'SUCCESS' : 'FAILED') . "\n";

    // Refresh and check
    $product->refresh();
    echo "\nAfter update:\n";
    echo "Title: {$product->title}\n";
    echo "Price: {$product->price}\n";

    // Verify the update worked
    if ($product->title === $newTitle && $product->price == $newPrice) {
        echo "\n✅ UPDATE SUCCESSFUL!\n";

        // Restore original values
        $product->update([
            'title' => $originalTitle,
            'price' => $originalPrice
        ]);
        echo "✅ Original values restored.\n";
    } else {
        echo "\n❌ UPDATE FAILED!\n";
        echo "Expected title: {$newTitle}\n";
        echo "Actual title: {$product->title}\n";
        echo "Expected price: {$newPrice}\n";
        echo "Actual price: {$product->price}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
