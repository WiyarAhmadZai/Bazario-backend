<?php

// Approve pending products
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Approving Pending Products ===\n";

try {
    // Get pending products
    $pendingProducts = Product::where('status', 'pending')->get();
    echo "Found " . $pendingProducts->count() . " pending products\n";

    if ($pendingProducts->count() > 0) {
        echo "\nPending products:\n";
        foreach ($pendingProducts as $product) {
            echo "- ID: {$product->id}, Title: {$product->title}, Seller: {$product->seller_id}\n";
        }

        // Get admin user
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            echo "❌ No admin user found!\n";
            exit(1);
        }

        echo "\nUsing admin: {$admin->name} (ID: {$admin->id})\n";

        // Approve all pending products
        $adminController = new \App\Http\Controllers\AdminController();
        auth()->login($admin);

        foreach ($pendingProducts as $product) {
            echo "\nApproving product: {$product->title}\n";

            $request = new \Illuminate\Http\Request();
            $response = $adminController->approveProduct($request, $product);

            echo "Approval response status: " . $response->getStatusCode() . "\n";

            // Verify the approval
            $product->refresh();
            echo "Product status after approval: {$product->status}\n";

            if ($product->status === 'approved') {
                echo "✅ Product approved successfully!\n";
            } else {
                echo "❌ Product approval failed!\n";
            }
        }

        // Test shop page API again
        echo "\n=== Testing Shop Page After Approval ===\n";

        $shopRequest = new \Illuminate\Http\Request();
        $shopRequest->merge([
            'page' => 1,
            'status' => 'approved',
            'per_page' => 10,
            'sort_by' => 'newest'
        ]);

        $productController = new \App\Http\Controllers\ProductController();
        $shopResponse = $productController->index($shopRequest);

        echo "Shop API Response Status: " . $shopResponse->getStatusCode() . "\n";
        $shopData = json_decode($shopResponse->getContent(), true);

        echo "Products in shop: " . count($shopData['data']) . "\n";
        echo "Total products: " . $shopData['total'] . "\n";

        if (count($shopData['data']) > 2) {
            echo "✅ SUCCESS! More products now showing in shop!\n";
        } else {
            echo "❌ Still only showing 2 products in shop\n";
        }
    } else {
        echo "No pending products to approve\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Product Approval Complete ===\n";
