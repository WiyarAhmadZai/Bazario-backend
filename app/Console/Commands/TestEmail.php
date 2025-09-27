<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?: '';

        $this->info("Testing email configuration...");
        $this->info("MAIL_MAILER: " . env('MAIL_MAILER', 'Not set'));
        $this->info("MAIL_HOST: " . env('MAIL_HOST', 'Not set'));
        $this->info("MAIL_PORT: " . env('MAIL_PORT', 'Not set'));
        $this->info("MAIL_USERNAME: " . env('MAIL_USERNAME', 'Not set'));
        $this->info("MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'Not set'));

        try {
            $this->info("Sending test email to: $email");

            Mail::raw('This is a test email from Luxury Marketplace to verify email configuration.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from Luxury Marketplace');
            });

            $this->info("✅ Email sent successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            $this->error("Line: " . $e->getLine());
            $this->error("File: " . $e->getFile());
        }
    }
}
