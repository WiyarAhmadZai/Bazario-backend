<?php

// Check users and test update
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Users and Testing Update ===\n";

try {
    // Check users
    $users = User::all();
    echo "Total users: " . $users->count() . "\n";

    foreach ($users as $user) {
        echo "User {$user->id}: {$user->name} (Role: {$user->role})\n";
    }

    // Get first user (any role)
    $user = $users->first();
    if (!$user) {
        echo "No users found!\n";
        exit(1);
    }

    // Get a product
    $product = Product::first();
    if (!$product) {
        echo "No products found!\n";
        exit(1);
    }

    echo "\nTesting update with user: {$user->name} (ID: {$user->id})\n";
    echo "Testing product: {$product->title} (ID: {$product->id})\n";

    // Test direct update
    $originalTitle = $product->title;
    $newTitle = $originalTitle . ' - DIRECT UPDATE';

    echo "Updating title from: {$originalTitle}\n";
    echo "Updating title to: {$newTitle}\n";

    $product->title = $newTitle;
    $saveResult = $product->save();

    echo "Save result: " . ($saveResult ? 'SUCCESS' : 'FAILED') . "\n";

    // Refresh and check
    $product->refresh();
    echo "After save - Title: {$product->title}\n";

    if ($product->title === $newTitle) {
        echo "✅ DIRECT UPDATE SUCCESSFUL!\n";

        // Restore original
        $product->title = $originalTitle;
        $product->save();
        echo "✅ Original title restored.\n";
    } else {
        echo "❌ DIRECT UPDATE FAILED!\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
