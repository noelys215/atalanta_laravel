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



    public function createCustomer($userInfo)
    {
        return $this->stripe->customers->create([
            'email' => $userInfo['email'],
            'name' => $userInfo['first_name'] . ' ' . $userInfo['last_name'],
            'phone' => $userInfo['telephone'],
            'address' => [
                'line1' => $userInfo['address'],
                'city' => $userInfo['city'],
                'state' => $userInfo['state'],
                'postal_code' => $userInfo['postal_code'],
                'country' => $userInfo['country'],
            ],
            'shipping' => [ // Include shipping address
                'name' => $userInfo['first_name'] . ' ' . $userInfo['last_name'],
                'address' => [
                    'line1' => $userInfo['address'],
                    'city' => $userInfo['city'],
                    'state' => $userInfo['state'],
                    'postal_code' => $userInfo['postal_code'],
                    'country' => $userInfo['country'],
                ],
            ],
        ]);
    }


    public function retrieveCheckoutSession($sessionId)
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId);
    }
}
