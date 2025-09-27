<?php
// Simple script to get verification code for testing purposes
// ONLY FOR DEVELOPMENT ENVIRONMENTS

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

// Check if email is provided
if ($argc < 2) {
    echo "Usage: php get_verification_code.php <email>\n";
    exit(1);
}

$email = $argv[1];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format\n";
    exit(1);
}

// Make API request to get verification code
$url = 'http://localhost:8000/api/get-verification-code';
$data = json_encode(['email' => $email]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Content-Length: ' . strlen($data)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo "Verification code retrieved successfully:\n";
    echo $response . "\n";
} else {
    echo "Failed to retrieve verification code. HTTP Code: $httpCode\n";
    echo $response . "\n";
}
