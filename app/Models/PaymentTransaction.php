<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'amount',
        'admin_share',
        'seller_share',
        'payment_method',
        'reference',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'admin_share' => 'decimal:2',
        'seller_share' => 'decimal:2',
    ];

    /**
     * Get the order associated with the payment transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
