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
            url('/return?session_id={CHECKOUT_SESSION_ID}'),
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

            return response()->json([
                'status' => $session->status,
                'customer_email' => $session->customer_email ?? null,
                'total_tax' => $session->total_details->amount_tax,
                'payment_method' => $session->payment_method_types[0] ?? null,
                'billing_address' => $session->billing_address_collection ?? null,
                'shipping_address' => $session->shipping_address_collection ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
