<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Products table structure:\n";
$columns = DB::select('DESCRIBE products');
foreach($columns as $column) {
    echo $column->Field . ' - ' . $column->Type . "\n";
}

echo "\nFavorites table structure:\n";
$columns = DB::select('DESCRIBE favorites');
foreach($columns as $column) {
    echo $column->Field . ' - ' . $column->Type . "\n";
}
