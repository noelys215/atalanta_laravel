<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'short_order_id',
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
        'customer_name',
        'customer_email',
    ];

    protected $casts = [
        'order_items' => 'array',
        'shipping_address' => 'array',
        'payment_result' => 'array',
    ];

    // Mutator to ensure order_items quantity and price are saved as numbers
    public function setOrderItemsAttribute($value)
    {
        foreach ($value as &$item) {
            $item['quantity'] = (int)$item['quantity'];
            $item['price'] = (float)$item['price'];
        }
        $this->attributes['order_items'] = json_encode($value);
    }

    // Mutators to ensure prices are saved as numbers
    public function setItemsPriceAttribute($value)
    {
        $this->attributes['items_price'] = (float)$value;
    }

    public function setTaxPriceAttribute($value)
    {
        $this->attributes['tax_price'] = (float)$value;
    }

    public function setShippingPriceAttribute($value)
    {
        $this->attributes['shipping_price'] = (float)$value;
    }

    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = (float)$value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
