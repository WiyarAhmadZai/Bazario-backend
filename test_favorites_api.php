<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

echo "Testing favorites API...\n";

// Get first user (Admin)
$user = User::first();
if (!$user) {
    echo "No users found\n";
    exit;
}

// Authenticate as this user
Auth::login($user);
echo "Authenticated as: " . $user->name . " (ID: " . $user->id . ")\n";

// Get user's favorite products
$favorites = $user->favoriteProducts()
    ->with(['category', 'seller'])
    ->get();

echo "Found " . $favorites->count() . " favorite products\n";

foreach ($favorites as $product) {
    echo "Product ID: " . $product->id . "\n";
    echo "Title: " . $product->title . "\n";
    echo "Images (raw): " . $product->images . "\n";
    echo "Image URLs: " . json_encode($product->getImageUrls()) . "\n";
    echo "---\n";
}
