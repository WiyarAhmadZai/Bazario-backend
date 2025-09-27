<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test user registration
use App\Models\User;

try {
    // Create a test user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'customer',
        'verified' => false,
        'email_verified' => false,
        'wallet_balance' => 0.00,
        'is_active' => true,
    ]);

    echo "User created successfully!\n";
    echo "User ID: " . $user->id . "\n";
    echo "User Email: " . $user->email . "\n";

    // Generate verification code
    $verificationCode = $user->generateVerificationCode();
    echo "Verification code generated: " . $verificationCode . "\n";

    // Check if code is stored in database
    $userFromDb = User::find($user->id);
    echo "Stored verification code: " . $userFromDb->verification_code . "\n";
    echo "Code expiry: " . $userFromDb->verification_code_expires_at . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
