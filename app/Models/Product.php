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
}
