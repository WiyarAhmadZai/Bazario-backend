<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
