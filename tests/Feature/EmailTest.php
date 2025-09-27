<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\VerificationCodeMail;
use App\Models\User;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_send_verification_email()
    {
        Mail::fake();

        // Create a user
        $user = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $verificationCode = '123456';

        // Send the email
        Mail::to($user->email)->send(new VerificationCodeMail($user, $verificationCode));

        // Assert that the email was sent
        Mail::assertSent(VerificationCodeMail::class, function ($mail) use ($user, $verificationCode) {
            return $mail->hasTo($user->email) &&
                $mail->code === $verificationCode;
        });
    }
}
