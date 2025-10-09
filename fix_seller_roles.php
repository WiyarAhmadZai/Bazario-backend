<?php

// Fix seller roles
require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Seller Roles ===\n";

try {
    // Update seller roles
    $sellersUpdated = User::whereIn('id', [2, 3, 4, 5])->update(['role' => 'seller']);
    echo "Updated {$sellersUpdated} seller roles to 'seller'\n";

    // Verify the update
    $sellers = User::whereIn('id', [2, 3, 4, 5])->get();
    echo "\nUpdated sellers:\n";
    foreach ($sellers as $seller) {
        echo "ID: {$seller->id}, Name: {$seller->name}, Role: {$seller->role}\n";
    }

    // Now test creating a product with a seller
    echo "\n=== Testing Product Creation with Fixed Role ===\n";

    $seller = User::find(2);
    auth()->login($seller);

    $request = new \Illuminate\Http\Request();
    $request->merge([
        'title' => 'Test Product with Fixed Role',
        'description' => 'This should be approved',
        'price' => 199.99,
        'discount' => 0.00,
        'stock' => 15,
        'category_id' => 1
    ]);

    $sellerController = new \App\Http\Controllers\SellerController();
    $response = $sellerController->createProduct($request);

    echo "Create Response Status: " . $response->getStatusCode() . "\n";
    $product = json_decode($response->getContent(), true);
    echo "Created Product Status: " . $product['status'] . "\n";

    if ($product['status'] === 'approved') {
        echo "✅ SUCCESS! Product created with 'approved' status\n";
    } else {
        echo "❌ FAILED! Product created with '{$product['status']}' status\n";
    }

    // Clean up
    \App\Models\Product::find($product['id'])->delete();
    echo "✅ Test product cleaned up.\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Role Fix Complete ===\n";
