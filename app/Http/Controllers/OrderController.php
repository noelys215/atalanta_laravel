<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // Create New Order
    public function addOrderItems(Request $request)
    {
        $request->validate([
            'orderItems' => 'required|array|min:1',
            'orderItems.*.name' => 'required|string|max:255',
            'orderItems.*.quantity' => 'required|integer|min:1',
            'orderItems.*.price' => 'required|numeric',
            'orderItems.*.image' => 'required|url',
            'orderItems.*.selectedSize' => 'required|string|max:255',
            'shippingAddress' => 'required|array',
            'paymentMethod' => 'required|string',
            'itemsPrice' => 'required|numeric',
            'taxPrice' => 'required|numeric',
            'shippingPrice' => 'required|numeric',
            'totalPrice' => 'required|numeric',
        ]);

        $order = new Order([
            'user_id' => Auth::id(),
            'order_items' => $request->orderItems,  // Pass as array
            'shipping_address' => $request->shippingAddress,  // Pass as array
            'payment_method' => $request->paymentMethod,
            'items_price' => $request->itemsPrice,
            'tax_price' => $request->taxPrice,
            'shipping_price' => $request->shippingPrice,
            'total_price' => $request->totalPrice,
            'is_paid' => true,
            'paid_at' => now(),
            'is_shipped' => false,
        ]);

        $order->save();

        return response()->json($order, 201);
    }

    // Get Order By ID
    public function getOrderById($id)
    {
        $order = Order::with('user:id,first_name,last_name,email')->find($id);

        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['error' => 'Order Not Found'], 404);
        }
    }

    // Update Order to Paid and Adjust Inventory
    public function updateOrderToPaid(Request $request, $id)
    {
        $order = Order::find($id);

        if ($order) {
            $order->is_paid = true;
            $order->paid_at = now();
            $order->payment_result = json_encode([
                'id' => $request->input('_id'),
                'status' => $request->input('status'),
                'update_time' => $request->input('update_time'),
                'email_address' => $request->input('payer.email_address'),
            ]);

            $order->save();

            // Adjust inventory
            foreach (json_decode($order->order_items, true) as $item) {
                $product = Product::where('name', $item['name'])->first();
                if ($product) {
                    $inventory = $product->inventory;
                    foreach ($inventory as &$invItem) {
                        if ($invItem['size'] == $item['selectedSize']) {
                            Log::info('Adjusting inventory for product: ' . $product->name . ', size: ' . $item['selectedSize'] . ', quantity before: ' . $invItem['quantity']);
                            $invItem['quantity'] -= $item['quantity'];
                            Log::info('Quantity after adjustment: ' . $invItem['quantity']);
                        }
                    }
                    $product->inventory = $inventory;
                    $product->save();
                }
            }

            return response()->json($order);
        } else {
            return response()->json(['error' => 'Order Not Found'], 404);
        }
    }

    // Update Order to Shipped
    public function updateOrderToShipped($id)
    {
        $order = Order::find($id);

        if ($order) {
            $order->is_shipped = true;
            $order->shipped_at = now();

            $order->save();

            return response()->json($order);
        } else {
            return response()->json(['error' => 'Order Not Found'], 404);
        }
    }

    // Get User Orders
    public function getMyOrders()
    {

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $orders = Order::where('user_id', $userId)->get();

        if ($orders->isEmpty()) {
            return response()->json(['error' => 'No orders found for user'], 404);
        }

        return response()->json($orders);
    }


    // Get All Orders (Admin)
    public function getOrders()
    {
        $orders = Order::with('user:id,first_name,last_name')->get();
        return response()->json($orders);
    }
}
