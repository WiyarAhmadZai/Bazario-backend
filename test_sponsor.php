<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test if sponsor column exists
    $columns = DB::select("SHOW COLUMNS FROM products LIKE 'sponsor'");

    if (empty($columns)) {
        echo "Sponsor column does not exist. Running migration...\n";

        // Run the migration
        $exitCode = Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_10_07_150228_add_sponsor_columns_to_products_table.php'
        ]);

        echo "Migration result: " . $exitCode . "\n";

        // Check again
        $columns = DB::select("SHOW COLUMNS FROM products LIKE 'sponsor'");
        if (!empty($columns)) {
            echo "Sponsor column created successfully!\n";
        } else {
            echo "Failed to create sponsor column.\n";
        }
    } else {
        echo "Sponsor column exists!\n";
    }

    // Test sponsored posts query
    $sponsoredProducts = DB::table('products')
        ->where('sponsor', true)
        ->where('sponsor_start_time', '<=', now())
        ->where('sponsor_end_time', '>=', now())
        ->where('status', 'approved')
        ->count();

    echo "Active sponsored products: " . $sponsoredProducts . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
