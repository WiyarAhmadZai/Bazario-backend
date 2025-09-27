<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test email sending
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

try {
    // Get the test user we created
    $user = User::where('email', 'test@example.com')->first();

    if (!$user) {
        echo "Test user not found!\n";
        exit(1);
    }

    echo "Found user: " . $user->name . " (" . $user->email . ")\n";
    echo "Current verification code: " . $user->verification_code . "\n";

    // Test sending email
    echo "Testing email sending...\n";

    // This should log the email since we're using the log mailer
    Mail::to($user->email)->send(new VerificationCodeMail($user, $user->verification_code, 'registration'));

    echo "Email sent successfully! Check the log file for details.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
