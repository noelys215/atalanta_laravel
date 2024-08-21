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

        // Generate a short order ID and store it in the metadata
        $shortOrderId = $this->generateShortOrderId($checkoutSession->id);
        $this->stripe->checkout->sessions->update($checkoutSession->id, [
            'metadata' => ['short_order_id' => $shortOrderId],
        ]);

        return $checkoutSession;
    }

    public function retrieveCheckoutSession($sessionId)
    {
        // Retrieve the session with expanded line items
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['line_items']
        ]);

        // Ensure 'line_items' exists in the session data
        if (!isset($session->line_items)) {
            $session->line_items = new \stdClass(); // Initialize it as an empty object if not present
        }

        // Now retrieve the product details separately
        foreach ($session->line_items->data as &$item) {
            $product = $this->stripe->products->retrieve($item->price->product);
            $item->product_details = $product; // Attach the product details to the item
        }

        return [
            'session' => $session,
            'short_order_id' => $session->metadata['short_order_id'] ?? null, // Include the short_order_id
        ];
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

    // Function to generate a shortened order ID
    protected function generateShortOrderId($longOrderId)
    {
        return substr(hash('sha256', $longOrderId), 0, 8);
    }

    public function retrieveSessionByEmailAndOrderId($email, $shortOrderId)
    {
        // List all sessions and expand customer details
        $sessions = $this->stripe->checkout->sessions->all([
            'limit' => 100,
            'expand' => ['data.line_items'],
        ]);

        // Filter the sessions to find one with the matching short_order_id and email
        foreach ($sessions->data as $session) {
            if (
                isset($session->metadata['short_order_id']) &&
                $session->metadata['short_order_id'] === $shortOrderId &&
                isset($session->customer_details->email) &&
                $session->customer_details->email === $email
            ) {
                return $this->retrieveCheckoutSession($session->id);
            }
        }

        return null; // Return null if no matching session is found
    }

}
