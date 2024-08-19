<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripeService;

class StripeController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'line_items' => 'required|array',
            'user_info' => 'required|array', // Validate that user_info is passed
        ]);

        $userInfo = $request->input('user_info');

        $checkoutSession = $this->stripeService->createCheckoutSession(
            $request->input('line_items'),
            url('http://localhost:5173/return?session_id={CHECKOUT_SESSION_ID}'),
            $userInfo
        );

        return response()->json([
            'sessionId' => $checkoutSession->id,
            'clientSecret' => $checkoutSession->client_secret
        ]);
    }

    public function retrieveCheckoutSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $session = $this->stripeService->retrieveCheckoutSession($request->input('session_id'));

            // Extract line items details including images
            $lineItems = [];
            foreach ($session->line_items->data as $item) {
                $product = $item->price->product;
                $lineItems[] = [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->amount_total / 100, // Convert to dollars if in cents
                    'image' => $product->images[0] ?? null // Assuming there is at least one image
                ];
            }

            return response()->json([
                'status' => $session->status,
                'customer_name' => $session->customer_details->name ?? null,
                'customer_email' => $session->customer_email ?? null,
                'billing_address' => $session->customer_details->address ?? null,
                'shipping_address' => $session->shipping_details ?? null,
                'order_details' => [
                    'line_items' => $lineItems,
                    'shipping_cost' => $session->total_details->amount_shipping / 100, // Convert to dollars if in cents
                    'tax' => $session->total_details->amount_tax / 100, // Convert to dollars if in cents
                    'total_price' => $session->amount_total / 100, // Convert to dollars if in cents
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
