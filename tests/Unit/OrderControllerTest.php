<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PlatziProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    #[Test]
    public function it_can_create_order_items()
    {
        $product = $this->seedPlatziProduct();

        $orderData = [
            'orderItems' => [
                [
                    'name' => $product->name,
                    'quantity' => 2,
                    'price' => 100.0,
                    'image' => $product->image[0] ?? 'https://example.com/product1.jpg',
                    'selectedSize' => 'M',
                ],
                ['name' => 'Product 2', 'quantity' => 1, 'price' => 50.0, 'image' => 'https://example.com/product2.jpg', 'selectedSize' => 'L']
            ],
            'shippingAddress' => [
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postalCode' => '10001',
                'country' => 'USA'
            ],
            'paymentMethod' => 'Credit Card',
            'itemsPrice' => 150.0,
            'taxPrice' => 15.0,
            'shippingPrice' => 10.0,
            'totalPrice' => 175.0,
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id, 'total_price' => 175.0]);
    }

    #[Test]
    public function it_can_get_order_by_id()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertJson(['id' => $order->id, 'user_id' => $this->user->id]);
    }

    #[Test]
    public function it_returns_404_if_order_not_found()
    {
        $response = $this->getJson('/api/orders/9999');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Order Not Found']);
    }


    #[Test]
    public function it_can_get_user_orders()
    {
        $orders = Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders/myorders');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    #[Test]
    public function it_returns_404_if_user_has_no_orders()
    {
        $response = $this->getJson('/api/orders/myorders');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'No orders found for user']);
    }

    private function seedPlatziProduct(): Product
    {
        Http::fake([
            'https://api.escuelajs.co/api/v1/products*' => Http::response([
                [
                    'id' => 999,
                    'title' => 'Seeded Product',
                    'description' => 'From seed',
                    'price' => 25.0,
                    'images' => ['https://example.com/seeded.jpg'],
                    'category' => ['name' => 'tops'],
                ],
            ], 200),
        ]);

        config(['seed_sources.platzi.default_limit' => 1]);

        $this->seed(PlatziProductSeeder::class);

        return Product::firstOrFail();
    }


}
