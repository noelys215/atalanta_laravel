<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Notifications\OrderPaidNotification;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Models\Order;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

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
            'user_info' => 'nullable|array',
        ]);

        $userInfo = $request->input('user_info');

        try {
            $clientUrl = rtrim(env('APP_CLIENT_URL', 'http://localhost:5173'), '/');

            $checkoutSession = $this->stripeService->createCheckoutSession(
                $request->input('line_items'),
                "{$clientUrl}/return?session_id={CHECKOUT_SESSION_ID}",
                $userInfo
            );

            return response()->json([
                'sessionId' => $checkoutSession->id,
                'clientSecret' => $checkoutSession->client_secret
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create Stripe checkout session', [
                'error_message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to create checkout session'], 500);
        }
    }

    public function retrieveCheckoutSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {

            $sessionData = $this->stripeService->retrieveCheckoutSession($request->input('session_id'));
            $session = $sessionData['session'];
            $shortOrderId = $sessionData['short_order_id'];


            // Check if the order already exists
            $existingOrder = Order::where('short_order_id', $shortOrderId)->first();

            if ($existingOrder) {
                return $this->formatOrderResponse($existingOrder, $session);
            }

            // Extract line items details including images
            $lineItems = [];
            if (isset($session->line_items->data)) {
                foreach ($session->line_items->data as $item) {
                    $product = $item->product_details; // Access product details attached in service
                    $lineItems[] = [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'price' => $item->amount_total / 100, // Convert to dollars if in cents
                        'image' => $product->images[0] ?? null, // Assuming there is at least one image
                        'size' => $product->metadata['selectedSize'] ?? null
                    ];
                }
            } else {
                \Log::warning('No line items found in Stripe session', ['session_id' => $request->input('session_id')]);
            }

            // Determine if the user is authenticated
            $userId = Auth::check() ? Auth::id() : null;

            // Create a new order
            try {
            $shippingDetails = $session->shipping_details ?? null;
            $shippingAddress = $shippingDetails->address ?? ($session->customer_details->address ?? null);

            $trackingNumber = 'EA' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT) . 'US';

            $order = Order::create([
                    'user_id' => $userId,
                    'short_order_id' => $shortOrderId,
                    'tracking_number' => $trackingNumber,
                    'order_items' => $lineItems,
                    'shipping_address' => [
                        'address' => [
                            'line1' => $shippingAddress->line1 ?? null,
                            'line2' => $shippingAddress->line2 ?? null,
                            'city' => $shippingAddress->city ?? null,
                            'state' => $shippingAddress->state ?? null,
                            'postal_code' => $shippingAddress->postal_code ?? null,
                            'country' => $shippingAddress->country ?? null,
                        ],
                        'name' => $shippingDetails->name ?? ($session->customer_details->name ?? null),
                    ],
                    'payment_method' => 'Stripe',
                    'items_price' => array_sum(array_column($lineItems, 'price')),
                    'tax_price' => ($session->total_details->amount_tax ?? 0) / 100,
                    'shipping_price' => ($session->total_details->amount_shipping ?? 0) / 100,
                    'total_price' => $session->amount_total / 100,
                    'is_paid' => true,
                    'paid_at' => now(),
                    'is_shipped' => false,
                    'is_delivered' => false,
                    'customer_name' => $session->customer_details->name ?? null,
                    'customer_email' => $session->customer_details->email ?? ($session->customer_email ?? null),
                    'payment_result' => json_encode($session->payment_intent) ? 'Complete' : 'Incomplete',
                ]);

                // Send email notification
                try {
                    Notification::route('mail', $order->customer_email)
                        ->notify(new OrderPaidNotification($order));
                    \Log::info('Order paid email sent successfully', ['order_id' => $order->id, 'email' => $order->customer_email]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send order paid email', [
                        'order_id' => $order->id,
                        'email' => $order->customer_email,
                        'error_message' => $e->getMessage(),
                    ]);
                }

                \Log::info($order);

                // Adjust inventory
                $inventoryAdjusted = false;
                foreach ($lineItems as $item) {
                    $product = Product::where('name', $item['description'])->first();
                    if ($product) {
                        $inventory = $product->inventory;
                        foreach ($inventory as &$invItem) {
                            if ($invItem['size'] == $item['size']) {
                                \Log::info('Adjusting inventory for product: ' . $product->name . ', size: ' . $item['size'] . ', quantity before: ' . $invItem['quantity']);
                                $invItem['quantity'] -= $item['quantity'];
                                \Log::info('Quantity after adjustment: ' . $invItem['quantity']);
                            }
                        }
                        $product->inventory = $inventory;
                        $product->save();
                        $inventoryAdjusted = true;
                    }
                }

                if ($inventoryAdjusted) {
                    $order->forceFill(['inventory_adjusted_at' => now()])->save();
                }

                \Log::info('Order created successfully', ['order_id' => $order->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to create order', [
                    'session_id' => $request->input('session_id'),
                    'error_message' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Failed to create order'], 500);
            }

            return $this->formatOrderResponse($order, $session)->header('X-Clear-Cart', 'true');
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve Stripe checkout session or create order', [
                'session_id' => $request->input('session_id'),
                'error_message' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function formatOrderResponse($order, $session)
    {
        try {
            $shippingDetails = $session->shipping_details ?? null;
            $fallbackAddress = $session->customer_details->address ?? null;
            $shippingDetails = $shippingDetails ?: ($fallbackAddress ? (object) [
                'name' => $session->customer_details->name ?? null,
                'address' => $fallbackAddress,
            ] : null);

            return response()->json([
                'status' => $session->status,
                'customer_name' => $session->customer_details->name ?? null,
                'customer_email' => $session->customer_details->email ?? ($session->customer_email ?? null),
                'billing_address' => $session->customer_details->address ?? null,
                'shipping_address' => $shippingDetails,
                'order_date' => $session->created,
                'order_details' => [
                    'line_items' => $order->order_items,
                    'shipping_cost' => $session->total_details->amount_shipping / 100, // Convert to dollars if in cents
                    'tax' => $session->total_details->amount_tax / 100, // Convert to dollars if in cents
                    'total_price' => $session->amount_total / 100, // Convert to dollars if in cents
                ],
                'is_shipped' => (bool) $order->is_shipped,
                'is_delivered' => (bool) $order->is_delivered,
                'tracking_number' => $order->tracking_number,
                'short_order_id' => $order->short_order_id, // Include the short_order_id in the response
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to format order response', [
                'order_id' => $order->id,
                'error_message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to format order response'], 500);
        }
    }

    public function getOrderHistoryByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Use the stripeService to get sessions
            $sessions = $this->stripeService->retrieveSessionsByEmail($request->input('email'));

            $orders = [];
            foreach ($sessions as $session) { // Loop through filtered sessions
                $lineItems = [];
                foreach ($session->line_items->data as $item) {
                    // Retrieve product details for each line item
                    $product = $this->stripeService->retrieveCheckoutSession($session->id)['session']->line_items->data[0]->product_details;

                    $lineItems[] = [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'price' => $item->amount_total / 100, // Convert to dollars if in cents
                        'image' => $product->images[0] ?? null // Assuming there is at least one image
                    ];
                }

                $orders[] = [
                    'id' => $session->id,
                    'short_order_id' => $session->metadata['short_order_id'] ?? null,
                    'status' => $session->status,
                    'created' => $session->created,
                    'total_price' => $session->amount_total / 100,
                    'line_items' => $lineItems,
                ];
            }

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOrderByEmailAndOrderId(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'order_id' => 'required|string',
        ]);

        try {
            $sessionData = $this->stripeService->retrieveSessionByEmailAndOrderId(
                $request->input('email'),
                $request->input('order_id')
            );

            if (!$sessionData) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $session = $sessionData['session'];
            $shortOrderId = $sessionData['short_order_id'];
            $shippingDetails = $session['shipping_details'] ?? null;
            $fallbackAddress = $session['customer_details']['address'] ?? null;
            $order = Order::where('short_order_id', $shortOrderId)->first();

            if (! $shippingDetails && $fallbackAddress) {
                $shippingDetails = [
                    'name' => $session['customer_details']['name'] ?? null,
                    'address' => $fallbackAddress,
                ];
            }

            // Ensure line_items key exists
            $lineItems = [];
            if (isset($session['line_items']) && isset($session['line_items']['data'])) {
                foreach ($session['line_items']['data'] as $item) {
                    $product = $item['product_details']; // Access product details attached in service
                    $lineItems[] = [
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'price' => $item['amount_total'] / 100, // Convert to dollars if in cents
                        'image' => $product['images'][0] ?? null // Assuming there is at least one image
                    ];
                }
            }

            return response()->json([
                'status' => $session['status'],
                'customer_name' => $session['customer_details']['name'] ?? null,
                'customer_email' => $session['customer_details']['email'] ?? ($session['customer_email'] ?? null),
                'billing_address' => $session['customer_details']['address'] ?? null,
                'shipping_address' => $shippingDetails,
                'order_date' => $session['created'],
                'order_details' => [
                    'line_items' => $lineItems,
                    'shipping_cost' => $session['total_details']['amount_shipping'] / 100, // Convert to dollars if in cents
                    'tax' => $session['total_details']['amount_tax'] / 100, // Convert to dollars if in cents
                    'total_price' => $session['amount_total'] / 100, // Convert to dollars if in cents
                ],
                'is_shipped' => (bool) ($order?->is_shipped ?? false),
                'is_delivered' => (bool) ($order?->is_delivered ?? false),
                'tracking_number' => $order?->tracking_number,
                'short_order_id' => $shortOrderId, // Include the short_order_id in the response
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
