<?php

// Check user roles
require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== User Roles Check ===\n";

try {
    $users = User::all();

    echo "User ID | Name | Email | Role\n";
    echo "--------|------|-------|-----\n";

    foreach ($users as $user) {
        echo "{$user->id} | {$user->name} | {$user->email} | {$user->role}\n";
    }

    // Check if there are any admin users
    $adminUsers = User::where('role', 'admin')->count();
    echo "\nAdmin users: {$adminUsers}\n";

    // Check if there are any seller users
    $sellerUsers = User::where('role', 'seller')->count();
    echo "Seller users: {$sellerUsers}\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== User Roles Check Complete ===\n";
