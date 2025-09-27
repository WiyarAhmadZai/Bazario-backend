<?php
// Test email sending
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Send a test email
    Mail::raw('This is a test email from Luxury Marketplace', function (Message $message) {
        $message->to('muhammadayoubsalih@gmail.com')
            ->subject('Test Email');
    });

    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
