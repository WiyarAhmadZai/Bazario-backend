<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class ShowVerificationCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show verification codes for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $unverifiedUsers = User::where('email_verified', false)
            ->whereNotNull('verification_code')
            ->get(['id', 'name', 'email', 'verification_code', 'verification_code_expires_at']);

        if ($unverifiedUsers->isEmpty()) {
            $this->info('No unverified users with verification codes found.');
            return;
        }

        $this->info('Unverified Users and their Verification Codes:');
        $this->line('');

        foreach ($unverifiedUsers as $user) {
            $expiresAt = $user->verification_code_expires_at;
            $isExpired = $expiresAt && Carbon::now()->gt($expiresAt);

            $this->line("User: {$user->name} ({$user->email})");
            $this->line("Code: {$user->verification_code}");

            if ($isExpired) {
                $this->error('Status: EXPIRED');
            } else {
                $this->info('Status: VALID');
                if ($expiresAt) {
                    $this->line("Expires: {$expiresAt->format('Y-m-d H:i:s')}");
                }
            }
            $this->line('---');
        }

        return Command::SUCCESS;
    }
}
