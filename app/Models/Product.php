<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\CategoryEnum;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seller_id',
        'title',
        'slug',
        'description',
        'price',
        'discount',
        'stock',
        'images',
        'category_id',
        'category_enum',
        'status',
        'is_featured',
        'view_count', // Add view counter
        'sponsor',
        'sponsor_start_time',
        'sponsor_end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'category_enum' => CategoryEnum::class,
        'view_count' => 'integer', // Cast view_count as integer
        'sponsor' => 'boolean',
        'sponsor_start_time' => 'datetime',
        'sponsor_end_time' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate slug when creating a product
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
            // Set default view count to 0
            if (empty($product->view_count)) {
                $product->view_count = 0;
            }
        });

        // Update slug when updating a product title
        static::updating(function ($product) {
            if ($product->isDirty('title') && !empty($product->title)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the seller (user) that owns the product.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the cart items for the product.
     */
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the wishlist items for the product.
     */
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the likes for the product.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the favorites for the product.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the users who favorited this product.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Calculate the discounted price.
     */
    public function getDiscountedPriceAttribute()
    {
        return $this->price - ($this->discount ?? 0);
    }

    /**
     * Get the category enum options.
     */
    public static function getCategoryEnumOptions()
    {
        return CategoryEnum::toArray();
    }

    /**
     * Scope for active sponsored products
     */
    public function scopeActiveSponsored($query)
    {
        return $query->where('sponsor', true)
            ->where('sponsor_start_time', '<=', now())
            ->where('sponsor_end_time', '>=', now())
            ->where('status', 'approved');
    }

    /**
     * Check if product is currently sponsored
     */
    public function isCurrentlySponsored()
    {
        return $this->sponsor &&
            $this->sponsor_start_time &&
            $this->sponsor_end_time &&
            $this->sponsor_start_time <= now() &&
            $this->sponsor_end_time >= now();
    }

    /**
     * Check if sponsorship has expired
     */
    public function hasSponsorshipExpired()
    {
        return $this->sponsor &&
            $this->sponsor_end_time &&
            $this->sponsor_end_time < now();
    }

    /**
     * Get time remaining for sponsorship
     */
    public function getSponsorshipTimeRemaining()
    {
        if (!$this->isCurrentlySponsored()) {
            return null;
        }

        return $this->sponsor_end_time->diffForHumans();
    }

    /**
     * Get image URLs for the product
     */
    public function getImageUrls()
    {
        if (!$this->images) {
            return [];
        }

        $images = is_string($this->images) ? json_decode($this->images, true) : $this->images;

        if (!is_array($images)) {
            return [];
        }

        return array_map(function ($image) {
            if (str_starts_with($image, 'http')) {
                return $image;
            }
            // Use the correct API base URL for images
            return 'http://localhost:8000/storage/' . $image;
        }, $images);
    }

    /**
     * Get likes count accessor
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Get favorites count accessor
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }

    /**
     * Convert product to post format for Posts page
     */
    public function toPostFormat()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'images' => $this->getImageUrls(),
            'visibility' => 'public',
            'is_published' => true,
            'user' => [
                'id' => $this->seller_id,
                'name' => $this->seller->name ?? 'Unknown Seller',
                'email' => $this->seller->email ?? '',
                'avatar' => $this->seller->avatar ? 'http://localhost:8000/storage/' . $this->seller->avatar : null,
            ],
            'likes_count' => $this->likes()->count(),
            'comments_count' => 0, // Products don't have comments yet
            'favorites_count' => 0, // Products don't have favorites yet
            'is_liked' => false,
            'is_favorited' => false,
            'time_ago' => $this->created_at->diffForHumans(),
            'image_urls' => $this->getImageUrls(),
            'is_sponsored' => true,
            'sponsor_end_time' => $this->sponsor_end_time,
            'sponsorship_time_remaining' => $this->getSponsorshipTimeRemaining(),
        ];
    }
}
