<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "Testing URL generation...\n";

$product = Product::find(163);
if ($product) {
    echo "Product: " . $product->title . "\n";
    echo "Images (raw): " . $product->images . "\n";
    echo "Image URLs: " . json_encode($product->getImageUrls()) . "\n";
    echo "App URL: " . config('app.url') . "\n";
    echo "Asset URL: " . asset('storage/test.jpg') . "\n";
}
