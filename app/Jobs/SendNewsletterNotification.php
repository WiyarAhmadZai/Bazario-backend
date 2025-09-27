<?php

namespace App\Jobs;

use App\Models\Newsletter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewsletterNotification implements ShouldQueue
{
    use Queueable;

    public $subject;
    public $content;
    public $postUrl;

    /**
     * Create a new job instance.
     */
    public function __construct($subject, $content, $postUrl = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->postUrl = $postUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get all active newsletter subscribers
            $subscribers = Newsletter::where('is_active', true)->get();

            foreach ($subscribers as $subscriber) {
                try {
                    Mail::send('emails.newsletter', [
                        'content' => $this->content,
                        'postUrl' => $this->postUrl,
                        'unsubscribeUrl' => url('/api/newsletter/unsubscribe?email=' . urlencode($subscriber->email))
                    ], function ($message) use ($subscriber) {
                        $message->to($subscriber->email)
                            ->subject($this->subject)
                            ->from(env('MAIL_FROM_ADDRESS', 'noreply@luxurystore.com'), 'Luxury Store');
                    });

                    Log::info('Newsletter sent successfully', [
                        'email' => $subscriber->email,
                        'subject' => $this->subject
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send newsletter', [
                        'email' => $subscriber->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Newsletter job failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
