<?php

// Test the exact API call that frontend makes
require_once 'vendor/autoload.php';

use App\Models\Product;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Frontend API Call ===\n";

try {
    // Simulate the exact API call the frontend makes
    $params = [
        'page' => 1,
        'status' => 'approved',
        'per_page' => 10,
        'sort_by' => 'newest'
    ];

    echo "API Parameters: " . json_encode($params) . "\n\n";

    // Test ProductController::index method directly
    $request = new \Illuminate\Http\Request();
    $request->merge($params);

    $productController = new \App\Http\Controllers\ProductController();
    $response = $productController->index($request);

    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";

    // Parse the response
    $data = json_decode($response->getContent(), true);

    if (isset($data['data']) && is_array($data['data'])) {
        echo "\n✅ API Response is correct!\n";
        echo "Products returned: " . count($data['data']) . "\n";
        echo "Total products: " . ($data['total'] ?? 'N/A') . "\n";
        echo "Current page: " . ($data['current_page'] ?? 'N/A') . "\n";
        echo "Last page: " . ($data['last_page'] ?? 'N/A') . "\n";

        if (count($data['data']) > 0) {
            echo "\nProducts in response:\n";
            foreach ($data['data'] as $product) {
                echo "- {$product['title']} (ID: {$product['id']}, Status: {$product['status']})\n";
            }
        } else {
            echo "\n❌ NO PRODUCTS IN API RESPONSE!\n";
        }
    } else {
        echo "\n❌ API Response format is incorrect!\n";
        echo "Expected: {data: [...], total: ..., current_page: ...}\n";
        echo "Got: " . json_encode($data) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== API Test Complete ===\n";
