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

// Path to Laravel's artisan command
$command = "cd " . __DIR__ . " && php artisan tinker --execute=\"\\\$user = App\\\Models\\\User::where('email', '{$email}')->first(); if (\\\$user) { echo 'Verification Code: ' . \\\$user->verification_code . PHP_EOL; echo 'Expires at: ' . \\\$user->verification_code_expires_at . PHP_EOL; } else { echo 'User not found\\n'; }\"";

echo "Executing: $command\n";
echo "----------------------------------------\n";
system($command);
