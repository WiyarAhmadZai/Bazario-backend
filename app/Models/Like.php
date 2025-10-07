<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id'
    ];

    /**
     * Get the user that owns the like
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that was liked
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
