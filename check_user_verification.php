<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

try {
    // Check if user exists
    $user = User::where('email', 'muhammadayoubsalih@gmail.com')->first();

    if (!$user) {
        echo "❌ User with email 'muhammadayoubsalih@gmail.com' not found in the database.\n";
        echo "Please make sure you registered with this exact email address.\n";
        exit(1);
    }

    echo "✅ User found!\n";
    echo "User ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Email Verified: " . ($user->email_verified ? 'Yes' : 'No') . "\n";
    echo "Account Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";

    if ($user->verification_code) {
        echo "Verification Code: " . $user->verification_code . "\n";
        echo "Code Expiry: " . $user->verification_code_expires_at . "\n";

        // Check if code is expired
        if ($user->isVerificationCodeExpired()) {
            echo "⚠️  Verification code has expired!\n";
        } else {
            echo "✅ Verification code is still valid.\n";
        }

        // Log the code for easy access
        Log::info('Retrieved verification code for user: ' . $user->email . ' - Code: ' . $user->verification_code);
        echo "\n📝 The verification code has been logged to storage/logs/laravel.log\n";
        echo "You can find it by searching for: 'muhammadayoubsalih@gmail.com'\n";
    } else {
        echo "❌ No verification code found for this user.\n";
        echo "This might mean the email was successfully sent or the code was already used.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
