<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Verify if email exists and is valid
     */
    private function verifyEmailExists($email): array
    {
        try {
            // Basic format validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'message' => 'Invalid email format.'];
            }

            // Extract domain from email
            $domain = substr(strrchr($email, "@"), 1);
            
            // Check if domain has MX record
            if (!checkdnsrr($domain, 'MX')) {
                return ['valid' => false, 'message' => 'Email domain does not exist or does not accept emails.'];
            }

            // Get MX records
            $mxRecords = [];
            getmxrr($domain, $mxRecords);
            
            if (empty($mxRecords)) {
                return ['valid' => false, 'message' => 'Email domain does not have valid mail servers.'];
            }

            // Try to connect to SMTP server to verify email
            $verified = $this->smtpVerifyEmail($email, $mxRecords[0]);
            
            if (!$verified) {
                return ['valid' => false, 'message' => 'This email address does not exist or is not accepting emails.'];
            }

            return ['valid' => true, 'message' => 'Email is valid and exists.'];
            
        } catch (\Exception $e) {
            // If verification fails due to network issues, allow subscription but log it
            \Log::warning('Email verification failed due to network issues', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return ['valid' => true, 'message' => 'Email format is valid (verification unavailable).'];
        }
    }

    /**
     * SMTP verification of email address
     */
    private function smtpVerifyEmail($email, $mxHost): bool
    {
        try {
            $timeout = 10;
            $socket = @fsockopen($mxHost, 25, $errno, $errstr, $timeout);
            
            if (!$socket) {
                return false;
            }

            // Read initial response
            $response = fgets($socket, 1024);
            
            // Send HELO command
            fputs($socket, "HELO localhost\r\n");
            $response = fgets($socket, 1024);
            
            // Send MAIL FROM command
            fputs($socket, "MAIL FROM: <noreply@luxurystore.com>\r\n");
            $response = fgets($socket, 1024);
            
            // Send RCPT TO command
            fputs($socket, "RCPT TO: <{$email}>\r\n");
            $response = fgets($socket, 1024);
            
            // Send QUIT command
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            // Check if email was accepted (2xx response code)
            return (substr($response, 0, 1) === '2');
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check for common disposable email domains
     */
    private function isDisposableEmail($email): bool
    {
        $disposableDomains = [
            '10minutemail.com', 'temp-mail.org', 'guerrillamail.com',
            'mailinator.com', 'yopmail.com', 'tempmail.net',
            'throwaway.email', 'getnada.com', 'maildrop.cc'
        ];
        
        $domain = substr(strrchr($email, "@"), 1);
        return in_array(strtolower($domain), $disposableDomains);
    }
    /**
     * Subscribe user to newsletter
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid email address.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;

            // Check if email already exists
            $existingSubscription = Newsletter::where('email', $email)->first();

            if ($existingSubscription) {
                if ($existingSubscription->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email is already subscribed to our newsletter.'
                    ], 409);
                } else {
                    // Reactivate subscription
                    $existingSubscription->update([
                        'is_active' => true,
                        'subscribed_at' => now()
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Welcome back! Your subscription has been reactivated.'
                    ], 200);
                }
            }

            // Create new subscription
            $subscription = Newsletter::create([
                'email' => $email,
                'is_active' => true,
                'subscribed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed! Welcome to our luxury community.',
                'data' => [
                    'email' => $subscription->email,
                    'subscribed_at' => $subscription->subscribed_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your subscription. Please try again.'
            ], 500);
        }
    }

    /**
     * Get all active subscribers (admin only)
     */
    public function getSubscribers(Request $request): JsonResponse
    {
        try {
            $subscribers = Newsletter::where('is_active', true)
                ->orderBy('subscribed_at', 'desc')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $subscribers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching subscribers.'
            ], 500);
        }
    }
}
