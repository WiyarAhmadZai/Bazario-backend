<?php

// Test database access directly
try {
    // Connect to the SQLite database
    $db = new PDO('sqlite:database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully\n";

    // Test a simple query
    $stmt = $db->query("SELECT count(*) as count FROM products WHERE status = 'approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Approved products count: " . $result['count'] . "\n";

    // Test another query
    $stmt = $db->query("SELECT id, title FROM products WHERE status = 'approved' LIMIT 3");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Sample products:\n";
    foreach ($products as $product) {
        echo "- " . $product['title'] . " (ID: " . $product['id'] . ")\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
