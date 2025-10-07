<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'followed_user_id',
        'notify_on_post',
        'notify_on_product'
    ];

    protected $casts = [
        'notify_on_post' => 'boolean',
        'notify_on_product' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function followedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_user_id');
    }
}
