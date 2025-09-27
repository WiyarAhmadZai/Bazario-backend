<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
}
