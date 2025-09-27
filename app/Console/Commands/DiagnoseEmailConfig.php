<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Mail\VerificationCodeMail;

class DiagnoseEmailConfig extends Command
{
    protected $signature = 'email:diagnose';
    protected $description = 'Diagnose email configuration and test sending';

    public function handle()
    {
        $this->info('🔍 Diagnosing Email Configuration...');
        $this->line('');

        // Check mail configuration
        $this->info('📧 Mail Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['MAIL_HOST', config('mail.mailers.smtp.host')],
                ['MAIL_PORT', config('mail.mailers.smtp.port')],
                ['MAIL_USERNAME', config('mail.mailers.smtp.username')],
                ['MAIL_PASSWORD', config('mail.mailers.smtp.password') ? '***SET***' : 'NOT SET'],
                ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['MAIL_FROM_NAME', config('mail.from.name')],
            ]
        );

        // Check if password is properly set (only for SMTP)
        if (config('mail.default') === 'smtp') {
            if (
                !config('mail.mailers.smtp.password') ||
                config('mail.mailers.smtp.password') === 'your_app_password_here' ||
                config('mail.mailers.smtp.password') === 'temp_placeholder_for_app_password'
            ) {
                $this->error('❌ MAIL_PASSWORD is not properly configured!');
                $this->line('Please set a valid Gmail App Password in your .env file.');
                $this->line('Steps to get Gmail App Password:');
                $this->line('1. Go to https://myaccount.google.com/security');
                $this->line('2. Enable 2-Factor Authentication');
                $this->line('3. Go to App Passwords');
                $this->line('4. Generate password for "Mail"');
                $this->line('5. Update MAIL_PASSWORD in .env file');
                return;
            }
        }

        // Test sending email
        $this->info('📤 Testing Email Sending...');
        $testEmail = 'mrwiyarahmadzai@gmail.com';

        try {
            // Create test user
            $testUser = new User();
            $testUser->name = 'Muhammad Hakeem Wiyar';
            $testUser->email = $testEmail;

            $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            $this->info("Sending test email to: {$testEmail}");
            $this->info("Verification code: {$verificationCode}");

            Mail::to($testEmail)->send(new VerificationCodeMail($testUser, $verificationCode, 'registration'));

            $this->info('✅ Email sent successfully!');

            if (config('mail.default') === 'log') {
                $this->line('📝 Email logged to: storage/logs/laravel.log');
                $this->line('Check the log file for the email content and verification code.');
            } else {
                $this->line('📧 Please check your email inbox for the verification code.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Email sending failed: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
