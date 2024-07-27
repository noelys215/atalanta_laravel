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
        'payment_method',
        'items_price',
        'tax_price',
        'shipping_price',
        'total_price',
        'is_paid',
        'paid_at',
        'payment_result',
        'is_shipped',
        'shipped_at',
    ];

    protected $casts = [
        'order_items' => 'array',
        'shipping_address' => 'array',
        'payment_result' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


