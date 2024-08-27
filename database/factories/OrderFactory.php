<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'order_items' => [
                ['name' => 'Product 1', 'quantity' => 2, 'price' => 100.0, 'image' => 'https://example.com/product1.jpg', 'selectedSize' => 'M'],
                ['name' => 'Product 2', 'quantity' => 1, 'price' => 50.0, 'image' => 'https://example.com/product2.jpg', 'selectedSize' => 'L']
            ],
            'shipping_address' => [
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA'
            ],
            'payment_method' => 'Credit Card',
            'items_price' => 150.0,
            'tax_price' => 15.0,
            'shipping_price' => 10.0,
            'total_price' => 175.0,
            'is_paid' => false,
            'paid_at' => null,
            'is_shipped' => false,
            'shipped_at' => null,
        ];
    }
}
