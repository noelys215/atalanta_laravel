<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_items',
        'shipping_address',
        'items_price',
        'payment_method',
        'payment_result',
        'tax_price',
        'shipping_price',
        'total_price',
        'is_paid',
        'paid_at',
        'is_shipped',
        'shipped_at',
        'is_delivered',
        'delivered_at',
    ];

    protected $casts = [
        'order_items' => 'array',
        'shipping_address' => 'array',
        'payment_result' => 'array',
        'is_paid' => 'boolean',
        'is_shipped' => 'boolean',
        'is_delivered' => 'boolean',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

