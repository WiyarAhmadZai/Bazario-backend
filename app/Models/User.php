<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'bio',
        'image',
        'date_of_birth',
        'address',
        'city',
        'country',
        'gender',
        'profession',
        'social_links',
        'role',
        'verified',
        'wallet_balance',
        'bank_account_info',
        'is_active',
        'last_login_at',
        'verification_code',
        'verification_code_expires_at',
        'email_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'verified' => 'boolean',
            'is_active' => 'boolean',
            'wallet_balance' => 'decimal:2',
            'bank_account_info' => 'array',
            'social_links' => 'array',
            'last_login_at' => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'email_verified' => 'boolean',
        ];
    }

    /**
     * Check if user is an admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a customer
     *
     * @return bool
     */
    public function isCustomer()
    {
        return $this->role === 'customer' || $this->role === null;
    }

    /**
     * Generate a verification code
     *
     * @return string
     */
    public function generateVerificationCode()
    {
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15), // 15 minutes expiry
        ]);

        // Log the verification code for development purposes
        if (app()->environment('local', 'development')) {
            Log::info('Verification code generated for user: ' . $this->email . ' - Code: ' . $code);
        }

        return $code;
    }

    /**
     * Verify the email verification code
     *
     * @param string $code
     * @return bool
     */
    public function verifyEmailCode($code)
    {
        if (
            $this->verification_code === $code &&
            $this->verification_code_expires_at &&
            $this->verification_code_expires_at->isFuture()
        ) {

            $this->update([
                'email_verified' => true,
                'verified' => true,
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Check if verification code is expired
     *
     * @return bool
     */
    public function isVerificationCodeExpired()
    {
        return !$this->verification_code_expires_at || $this->verification_code_expires_at->isPast();
    }

    /**
     * Get verification code for development/testing purposes
     * ONLY for non-production environments
     *
     * @return string|null
     */
    public function getVerificationCodeForTesting()
    {
        if (app()->environment('production')) {
            return null;
        }

        return $this->verification_code;
    }

    // Post relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function postFavorites()
    {
        return $this->hasMany(PostFavorite::class);
    }

    // Follow relationships
    public function followers()
    {
        return $this->hasMany(Follower::class, 'followed_id');
    }

    public function following()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    public function followingUsers()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    public function followerUsers()
    {
        return $this->belongsToMany(User::class, 'followers', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    // Notification relationships
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    // Helper methods
    public function isFollowing($userId)
    {
        return $this->following()->where('followed_id', $userId)->exists();
    }

    public function isFollowedBy($userId)
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }
}
