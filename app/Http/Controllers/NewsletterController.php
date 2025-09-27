<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\EmailValidationService;

class NewsletterController extends Controller
{
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

            $email = strtolower(trim($request->email));

            // Validate email with comprehensive checks
            $emailValidation = EmailValidationService::validateEmail($email);
            if (!$emailValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $emailValidation['message']
                ], 422);
            }

            // Check if email already exists in our database
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

            Log::info('New newsletter subscription', ['email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed! Welcome to our luxury community.',
                'data' => [
                    'email' => $subscription->email,
                    'subscribed_at' => $subscription->subscribed_at
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your subscription. Please try again.'
            ], 500);
        }
    }

    /**
     * Unsubscribe user from newsletter
     */
    public function unsubscribe(Request $request): JsonResponse
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

            $email = strtolower(trim($request->email));
            $subscription = Newsletter::where('email', $email)->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address not found in our newsletter list.'
                ], 404);
            }

            // Delete the subscription from database
            $subscription->delete();

            Log::info('Newsletter unsubscription', ['email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from newsletter. You will no longer receive updates.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Newsletter unsubscribe error', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if user is subscribed to newsletter
     */
    public function checkSubscription(Request $request): JsonResponse
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

            $email = strtolower(trim($request->email));
            $subscription = Newsletter::where('email', $email)->where('is_active', true)->first();

            return response()->json([
                'success' => true,
                'subscribed' => $subscription ? true : false,
                'subscription_date' => $subscription ? $subscription->subscribed_at : null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking subscription status.'
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
