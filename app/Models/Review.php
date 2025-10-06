<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'approved',
        'parent_id',
        'is_reply',
        'reply_count',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the review.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the parent review (for replies).
     */
    public function parent()
    {
        return $this->belongsTo(Review::class, 'parent_id');
    }

    /**
     * Get the replies for this review.
     */
    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get all replies recursively (for nested replies).
     */
    public function allReplies()
    {
        return $this->replies()->with('allReplies', 'user');
    }
}
