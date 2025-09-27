<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;

class TestEmailSending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending with verification code';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Create a test user
        $testUser = new User();
        $testUser->name = 'Test User';
        $testUser->email = $email;

        // Generate verification code
        $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $this->info("Sending test verification email to: {$email}");
        $this->info("Verification code: {$verificationCode}");

        try {
            // Send verification email
            Mail::to($email)->send(new VerificationCodeMail($testUser, $verificationCode, 'registration'));

            $this->info('✅ Email sent successfully!');
            $this->line('Check your email inbox or the Laravel log file for the email content.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
