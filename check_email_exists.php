<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    // Get the email from command line argument or use default
    $email = $argv[1] ?? 'muhammadayoubsalih@gmail.com';

    echo "Checking if email exists: " . $email . "\n";

    // Check if user exists
    $user = User::where('email', $email)->first();

    if ($user) {
        echo "✅ User found!\n";
        echo "User ID: " . $user->id . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Email Verified: " . ($user->email_verified ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ User with email '" . $email . "' not found in the database.\n";
        echo "This means you haven't registered with this email yet.\n";
        echo "Please register first before requesting verification code.\n";
    }

    // Also check using the exists query directly
    $exists = DB::table('users')->where('email', $email)->exists();
    echo "Email exists in database: " . ($exists ? 'Yes' : 'No') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
