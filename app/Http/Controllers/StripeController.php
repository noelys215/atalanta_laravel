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
            'price_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $checkoutSession = $this->stripeService->createCheckoutSession(
            $request->input('price_id'),
            $request->input('quantity'),
            url('/return?session_id={CHECKOUT_SESSION_ID}')
        );

        return response()->json(['clientSecret' => $checkoutSession->id]);
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
                'customer_email' => $session->customer_details->email ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
