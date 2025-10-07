<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follower;
use App\Models\NotificationSetting;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    /**
     * Follow or unfollow a user
     */
    public function follow(Request $request, $userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);

        // Can't follow yourself
        if ($user->id === $targetUser->id) {
            return response()->json([
                'message' => 'You cannot follow yourself',
                'error' => 'INVALID_ACTION'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $isFollowing = $user->isFollowing($targetUser->id);

            if ($isFollowing) {
                // Unfollow
                Follower::where('follower_id', $user->id)
                    ->where('followed_id', $targetUser->id)
                    ->delete();

                // Remove notification settings
                NotificationSetting::where('user_id', $user->id)
                    ->where('followed_user_id', $targetUser->id)
                    ->delete();

                $message = 'Unfollowed successfully';
                $action = 'unfollowed';
            } else {
                // Follow
                Follower::create([
                    'follower_id' => $user->id,
                    'followed_id' => $targetUser->id
                ]);

                // Create default notification settings
                NotificationSetting::create([
                    'user_id' => $user->id,
                    'followed_user_id' => $targetUser->id,
                    'notify_on_post' => true,
                    'notify_on_product' => false
                ]);

                $message = 'Followed successfully';
                $action = 'followed';
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'action' => $action,
                'is_following' => !$isFollowing,
                'followers_count' => $targetUser->followers_count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred',
                'error' => 'DATABASE_ERROR'
            ], 500);
        }
    }

    /**
     * Toggle notification settings for a followed user
     */
    public function toggleNotification(Request $request, $userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);

        // Check if user is following the target user
        if (!$user->isFollowing($targetUser->id)) {
            return response()->json([
                'message' => 'You must follow this user first',
                'error' => 'NOT_FOLLOWING'
            ], 400);
        }

        $notificationType = $request->input('type', 'post'); // 'post' or 'product'
        $enabled = $request->boolean('enabled', true);

        $setting = NotificationSetting::where('user_id', $user->id)
            ->where('followed_user_id', $targetUser->id)
            ->first();

        if ($setting) {
            $field = $notificationType === 'post' ? 'notify_on_post' : 'notify_on_product';
            $setting->update([$field => $enabled]);
        }

        return response()->json([
            'message' => $enabled ? 'Notifications enabled' : 'Notifications disabled',
            'notify_on_post' => $setting->notify_on_post ?? false,
            'notify_on_product' => $setting->notify_on_product ?? false
        ]);
    }

    /**
     * Get follow status and notification settings
     */
    public function getStatus($userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);

        $isFollowing = $user->isFollowing($targetUser->id);
        $notificationSettings = null;

        if ($isFollowing) {
            $notificationSettings = NotificationSetting::where('user_id', $user->id)
                ->where('followed_user_id', $targetUser->id)
                ->first();
        }

        return response()->json([
            'is_following' => $isFollowing,
            'followers_count' => $targetUser->followers_count,
            'following_count' => $targetUser->following_count,
            'notification_settings' => $notificationSettings
        ]);
    }

    /**
     * Get user's followers
     */
    public function getFollowers($userId)
    {
        $user = User::findOrFail($userId);
        $followers = $user->followerUsers()
            ->select('id', 'name', 'email', 'avatar', 'created_at')
            ->paginate(20);

        return response()->json($followers);
    }

    /**
     * Get user's following
     */
    public function getFollowing($userId)
    {
        $user = User::findOrFail($userId);
        $following = $user->followingUsers()
            ->select('id', 'name', 'email', 'avatar', 'created_at')
            ->paginate(20);

        return response()->json($following);
    }
}
