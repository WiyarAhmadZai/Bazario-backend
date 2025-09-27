<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

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

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'verified' => false,
                'wallet_balance' => 0.00,
                'is_active' => true,
            ]);

            // Create a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'image' => $user->image, // Will be null for new users
                    'date_of_birth' => $user->date_of_birth,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'gender' => $user->gender,
                    'profession' => $user->profession,
                    'role' => $user->role,
                    'verified' => $user->verified,
                    'wallet_balance' => $user->wallet_balance,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
                'message' => 'User registered successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
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

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid login credentials'
                ], 401);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Create a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'image' => $user->image, // Relative path as stored in database
                    'date_of_birth' => $user->date_of_birth,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'gender' => $user->gender,
                    'profession' => $user->profession,
                    'role' => $user->role,
                    'verified' => $user->verified,
                    'wallet_balance' => $user->wallet_balance,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
                'message' => 'User logged in successfully'
            ]);
        } catch (\Exception $e) {
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
            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'User logged out successfully'
            ]);
        } catch (\Exception $e) {
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
        try {
            // Get fresh user data from database
            $user = User::find($request->user()->id);

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'image' => $user->image, // Keep as stored in database (relative path)
                'date_of_birth' => $user->date_of_birth,
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
                'gender' => $user->gender,
                'profession' => $user->profession,
                'social_links' => $user->social_links,
                'role' => $user->role,
                'verified' => $user->verified,
                'wallet_balance' => $user->wallet_balance,
                'is_active' => $user->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user data',
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

            // Log the incoming request for debugging
            Log::info('Profile update request received:', [
                'user_id' => $user->id,
                'method' => $request->method(),
                'has_file' => $request->hasFile('image'),
                'all_data' => $request->all()
            ]);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'sometimes|string|max:20',
                'bio' => 'sometimes|string|max:1000',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
                'date_of_birth' => 'sometimes|date',
                'address' => 'sometimes|string|max:500',
                'city' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'gender' => 'sometimes|in:male,female,other',
                'profession' => 'sometimes|string|max:100',
                'social_links' => 'sometimes|array',
            ]);

            // Handle image upload
            $updateData = $request->only([
                'name',
                'email',
                'phone',
                'bio',
                'date_of_birth',
                'address',
                'city',
                'country',
                'gender',
                'profession',
                'social_links'
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();

                // Use move() method instead of storeAs() for more reliability
                $destinationPath = storage_path('app/public/images');

                // Ensure directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the uploaded file
                if ($image->move($destinationPath, $imageName)) {
                    $updateData['image'] = 'storage/images/' . $imageName;

                    Log::info('Image uploaded successfully:', [
                        'original_name' => $image->getClientOriginalName(),
                        'stored_name' => $imageName,
                        'relative_path' => $updateData['image'],
                        'file_exists' => file_exists($destinationPath . '/' . $imageName),
                        'file_size' => file_exists($destinationPath . '/' . $imageName) ? filesize($destinationPath . '/' . $imageName) : 0
                    ]);
                } else {
                    Log::error('Failed to move uploaded file');
                    throw new \Exception('Failed to upload image file');
                }
            }

            Log::info('About to update user with data:', $updateData);

            $user->update($updateData);

            // Log successful update
            Log::info('User updated successfully:', [
                'user_id' => $user->id,
                'updated_image' => $user->fresh()->image
            ]);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'image' => $user->fresh()->image, // Get fresh data from database
                    'date_of_birth' => $user->date_of_birth,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'gender' => $user->gender,
                    'profession' => $user->profession,
                    'social_links' => $user->social_links,
                    'role' => $user->role,
                    'verified' => $user->verified,
                    'wallet_balance' => $user->wallet_balance,
                    'is_active' => $user->is_active,
                ],
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Profile update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
