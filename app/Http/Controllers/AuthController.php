<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use App\Services\EmailValidationService;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if user with this email already exists but is not verified
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && !$existingUser->email_verified) {
            // User exists but email is not verified, redirect to verification
            $verificationCode = $existingUser->generateVerificationCode();

            // Send verification email
            $emailSent = $this->sendVerificationEmail($existingUser, $verificationCode);

            return response()->json([
                'message' => 'Account already exists but email not verified. Please check your email for verification code.',
                'email' => $existingUser->email,
                'verification_code' => !$emailSent ? $verificationCode : null, // Only expose code if email failed
                'requires_verification' => true,
                'redirect_to_verification' => true
            ], 200);
        }

        // If user exists and is verified, return error
        if ($existingUser && $existingUser->email_verified) {
            return response()->json([
                'message' => 'The email has already been taken.'
            ], 422);
        }

        // Validate email with comprehensive checks
        $emailValidation = EmailValidationService::validateEmail($request->email);
        if (!$emailValidation['valid']) {
            return response()->json([
                'message' => $emailValidation['message'],
                'error_type' => $emailValidation['error_type']
            ], 422);
        }

        try {
            // Create the user (but don't verify email yet)
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'verified' => false,
                'email_verified' => false,
                'wallet_balance' => 0.00,
                'is_active' => true,
            ]);

            // Generate verification code
            $verificationCode = $user->generateVerificationCode();

            // Send verification email
            $emailSent = $this->sendVerificationEmail($user, $verificationCode);

            return response()->json([
                'message' => $emailSent
                    ? 'Registration successful! Please check your email for the verification code.'
                    : 'Registration successful! Verification code generated but email delivery failed. Please contact support.',
                'email' => $user->email,
                'verification_code' => !$emailSent ? $verificationCode : null, // Only expose code if email failed
                'requires_verification' => true
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email code
     */
    public function verifyEmail(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:6',
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Verify the code
            if ($user->verifyEmailCode($request->verification_code)) {
                return response()->json([
                    'message' => 'Email verified successfully! You can now log in.',
                    'email_verified' => true
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Invalid or expired verification code'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Email verification failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Email verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user is already verified
            if ($user->email_verified) {
                return response()->json([
                    'message' => 'Email is already verified'
                ], 400);
            }

            // Generate new verification code
            $verificationCode = $user->generateVerificationCode();

            // Send verification email
            $emailSent = $this->sendVerificationEmail($user, $verificationCode);

            return response()->json([
                'message' => $emailSent
                    ? 'Verification code resent successfully! Please check your email.'
                    : 'Verification code generated but email delivery failed. Please contact support.',
                'verification_code' => !$emailSent ? $verificationCode : null, // Only expose code if email failed
            ], 200);
        } catch (\Exception $e) {
            Log::error('Resend verification code failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to resend verification code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if user exists
            if (!$user) {
                return response()->json([
                    'message' => 'These credentials do not match our records.'
                ], 401);
            }

            // Check if email is verified
            if (!$user->email_verified) {
                // Generate a new verification code
                $verificationCode = $user->generateVerificationCode();

                // Send verification email
                $emailSent = $this->sendVerificationEmail($user, $verificationCode);

                return response()->json([
                    'message' => 'Please verify your email before logging in.',
                    'email' => $user->email,
                    'verification_code' => !$emailSent ? $verificationCode : null, // Only expose code if email failed
                    'requires_verification' => true
                ], 401);
            }

            // Check if password is correct
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'These credentials do not match our records.'
                ], 401);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'message' => 'Your account has been deactivated. Please contact support.'
                ], 401);
            }

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            Log::error('Login failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Get user profile (public)
     */
    public function show($id)
    {
        try {
            // Find user by ID and only return public information
            $user = User::select('id', 'name', 'bio', 'created_at')
                ->where('id', $id)
                ->where('is_active', true)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json($user, 200);
        } catch (\Exception $e) {
            Log::error('Get user profile failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to fetch user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Validate the request
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'date_of_birth' => 'nullable|date',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'gender' => 'nullable|string|in:male,female,other',
                'profession' => 'nullable|string|max:100',
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Update other fields
            $user->update($request->only([
                'name',
                'bio',
                'date_of_birth',
                'address',
                'city',
                'country',
                'gender',
                'profession'
            ]));

            return response()->json($user, 200);
        } catch (\Exception $e) {
            Log::error('Update profile failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validate the request
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'message' => 'Password changed successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Change password failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification code for development/testing purposes
     * ONLY for non-production environments
     */
    public function getVerificationCode(Request $request)
    {
        // Only allow in non-production environments
        if (app()->environment('production')) {
            return response()->json([
                'message' => 'This endpoint is only available in development environments'
            ], 403);
        }

        // Validate the request
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Get the verification code
            $verificationCode = $user->getVerificationCodeForTesting();

            if (!$verificationCode) {
                return response()->json([
                    'message' => 'Verification code not found or expired'
                ], 404);
            }

            return response()->json([
                'verification_code' => $verificationCode
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get verification code failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to get verification code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail($user, $verificationCode)
    {
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($user, $verificationCode));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return false;
        }
    }
}
