<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Log;

try {
    echo "Starting complete registration test...\n";

    // Simulate the registration process exactly as in AuthController
    $userData = [
        'name' => 'Test Registration User',
        'email' => 'testregistration' . time() . '@example.com',
        'password' => Hash::make('password123'),
        'role' => 'customer',
        'verified' => false,
        'email_verified' => false,
        'wallet_balance' => 0.00,
        'is_active' => true,
    ];

    echo "Creating user with email: " . $userData['email'] . "\n";
    $user = User::create($userData);
    echo "User created with ID: " . $user->id . "\n";

    // Generate verification code
    echo "Generating verification code...\n";
    $verificationCode = $user->generateVerificationCode();
    echo "Verification code generated: " . $verificationCode . "\n";

    // Try to send verification email (this is where the issue might be)
    echo "Attempting to send verification email...\n";

    try {
        // Try to send the email
        Mail::to($user->email)->send(new VerificationCodeMail($user, $verificationCode, 'registration'));
        echo "✅ Email sent successfully!\n";
        $emailSent = true;
    } catch (\Exception $e) {
        // Log the error with full details
        echo "❌ Failed to send verification email: " . $e->getMessage() . "\n";
        echo "Exception class: " . get_class($e) . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "File: " . $e->getFile() . "\n";

        // Log the error
        Log::warning('Failed to send verification email: ' . $e->getMessage());

        // In development environment, log the verification code
        if (app()->environment('local', 'development')) {
            Log::info('Verification code for ' . $user->email . ': ' . $verificationCode);
            echo "Verification code logged for development: " . $verificationCode . "\n";
        }

        $emailSent = false;
    }

    // Show the result as the AuthController would
    $message = $emailSent
        ? 'Registration successful! Please check your email for the verification code.'
        : 'Registration successful! Verification code generated but email delivery failed. Please contact support.';

    echo "\n--- REGISTRATION RESULT ---\n";
    echo "Message: " . $message . "\n";
    echo "Email: " . $user->email . "\n";

    if (!$emailSent) {
        echo "Verification code (for development): " . $verificationCode . "\n";
    }

    echo "Requires verification: true\n";
} catch (\Exception $e) {
    echo "Error in registration process: " . $e->getMessage() . "\n";
    echo "Exception class: " . get_class($e) . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
