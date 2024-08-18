<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public function createCheckoutSession($priceId, $quantity = 1, $returnUrl)
    {
        return $this->stripe->checkout->sessions->create([
            'ui_mode' => 'embedded',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => $quantity,
            ]],
            'mode' => 'payment',
            'return_url' => $returnUrl,
        ]);
    }

    public function retrieveCheckoutSession($sessionId)
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId);
    }
}
