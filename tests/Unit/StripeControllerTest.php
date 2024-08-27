<?php

namespace Tests\Unit;

use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StripeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $stripeServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stripeServiceMock = $this->createMock(StripeService::class);
        $this->app->instance(StripeService::class, $this->stripeServiceMock);
    }

    public function test_create_checkout_session_success()
    {
        $this->stripeServiceMock->method('createCheckoutSession')
            ->willReturn((object)[
                'id' => 'test_session_id',
                'client_secret' => 'test_client_secret',
            ]);

        $response = $this->postJson('/api/stripe/create-checkout-session', [
            'line_items' => [
                ['price' => 'price_1', 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'sessionId' => 'test_session_id',
            'clientSecret' => 'test_client_secret',
        ]);
    }

    public function test_create_checkout_session_failure()
    {
        $this->stripeServiceMock->method('createCheckoutSession')
            ->will($this->throwException(new \Exception('Stripe error')));

        Log::shouldReceive('error')->once();

        $response = $this->postJson('/api/stripe/create-checkout-session', [
            'line_items' => [
                ['price' => 'price_1', 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Failed to create checkout session']);
    }


    public function test_get_order_by_email_and_order_id_success()
    {
        $mockSession = [
            'id' => 'test_session_id',
            'status' => 'complete',
            'created' => 1620000000,
            'amount_total' => 10000,
            'line_items' => [
                'data' => [
                    [
                        'description' => 'Test Product',
                        'quantity' => 1,
                        'amount_total' => 10000,
                        'product_details' => [
                            'images' => ['https://example.com/image.png'],
                        ]
                    ]
                ]
            ],
            'metadata' => [
                'short_order_id' => 'short_order_id'
            ],
            'customer_details' => [
                'name' => 'John Doe',
                'address' => [
                    'line1' => '123 Test Street',
                    'city' => 'Test City',
                    'state' => 'Test State',
                    'postal_code' => '12345',
                    'country' => 'US'
                ]
            ],
            'shipping_details' => [
                'address' => [
                    'line1' => '123 Test Street',
                    'city' => 'Test City',
                    'state' => 'Test State',
                    'postal_code' => '12345',
                    'country' => 'US'
                ]
            ],
            'total_details' => [
                'amount_tax' => 1000,
                'amount_shipping' => 500,
            ],
            'amount_total' => 10000,
            'customer_email' => 'johndoe@example.com',
        ];

        $this->stripeServiceMock->method('retrieveSessionByEmailAndOrderId')
            ->willReturn(['session' => $mockSession, 'short_order_id' => 'short_order_id']);

        $response = $this->postJson('/api/stripe/order-by-email-and-id', [
            'email' => 'johndoe@example.com',
            'order_id' => 'test_session_id'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'customer_name',
            'customer_email',
            'billing_address',
            'shipping_address',
            'order_date',
            'order_details' => [
                'line_items',
                'shipping_cost',
                'tax',
                'total_price'
            ],
            'short_order_id'
        ]);
    }
}
