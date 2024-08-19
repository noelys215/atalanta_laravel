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

    public function createCheckoutSession($lineItems, $returnUrl, $userInfo = null)
    {
        // Initialize session data with automatic tax enabled
        $sessionData = [
            'ui_mode' => 'embedded',
            'line_items' => $lineItems,
            'mode' => 'payment',
            'payment_method_types' => ['card', 'klarna', 'afterpay_clearpay'],
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['US', 'CA'], // Specify the allowed countries
            ],
            'shipping_options' => [
                [
                    'shipping_rate' => 'shr_1PpIuHFi1i8CbapdJV1uD6e7', // Replace with your actual shipping rate ID from Stripe
                ],
            ],
            'automatic_tax' => [
                'enabled' => true, // Enable automatic tax calculation
            ],
            'return_url' => $returnUrl,
        ];

        // Prefill the email if userInfo exists
        if ($userInfo && isset($userInfo['email'])) {
            $sessionData['customer_email'] = $userInfo['email'];
        }

        // Create the checkout session
        $checkoutSession = $this->stripe->checkout->sessions->create($sessionData);

        return $checkoutSession;
    }

    public function retrieveCheckoutSession($sessionId)
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['line_items.data.price.product']
        ]);

        return $session;
    }


    public function retrieveLineItems($sessionId)
    {
        return $this->stripe->checkout->sessions->allLineItems($sessionId, ['limit' => 100]);
    }

    public function retrieveSessionsByEmail($email)
    {
        $sessions = $this->stripe->checkout->sessions->all([
            'limit' => 100,
            'expand' => ['data.line_items'],
            'customer_details' => ['email' => $email]
        ]);

        // Filter sessions for paid orders only
        $paidSessions = array_filter($sessions->data, function ($session) {
            return $session->payment_status === 'paid';
        });

        return $paidSessions;
    }


}
