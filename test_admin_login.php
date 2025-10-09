<?php

// Test admin login functionality
echo "=== Testing Admin Login ===\n\n";

// Test admin login endpoint
echo "1. Testing admin login endpoint...\n";
$loginUrl = "http://127.0.0.1:8000/api/login";
$loginData = json_encode([
    'email' => 'admin@admin.com',
    'password' => 'password' // Default password
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $loginData,
        'timeout' => 10
    ]
]);

$response = @file_get_contents($loginUrl, false, $context);
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['token'])) {
        echo "✅ Admin login successful!\n";
        echo "Token received: " . substr($data['token'], 0, 20) . "...\n";
        echo "User role: " . ($data['user']['role'] ?? 'unknown') . "\n";

        // Test admin dashboard with token
        echo "\n2. Testing admin dashboard with token...\n";
        $dashboardUrl = "http://127.0.0.1:8000/api/admin/dashboard";
        $authContext = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$data['token']}\r\nContent-Type: application/json",
                'timeout' => 10
            ]
        ]);

        $dashboardResponse = @file_get_contents($dashboardUrl, false, $authContext);
        if ($dashboardResponse) {
            $dashboardData = json_decode($dashboardResponse, true);
            echo "✅ Admin dashboard accessible!\n";
            echo "Dashboard data: " . json_encode($dashboardData) . "\n";
        } else {
            echo "❌ Admin dashboard still failing\n";
        }
    } else {
        echo "❌ Login failed: " . $response . "\n";
    }
} else {
    echo "❌ Could not connect to login endpoint\n";
}

echo "\n=== Test Complete ===\n";
echo "If login works, the admin dashboard should work in the browser.\n";
echo "Make sure to:\n";
echo "1. Login as admin@admin.com\n";
echo "2. Use the correct password\n";
echo "3. Access admin dashboard\n";
