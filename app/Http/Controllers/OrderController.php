<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Create New Order
    public function addOrderItems(Request $request)
    {
        $request->validate([
            'orderItems' => 'required|array|min:1',
            'shippingAddress' => 'required|array',
            'paymentMethod' => 'required|string',
            'itemsPrice' => 'required|numeric',
            'taxPrice' => 'required|numeric',
            'shippingPrice' => 'required|numeric',
            'totalPrice' => 'required|numeric',
        ]);

        $order = new Order([
            'user_id' => Auth::id(),
            'order_items' => json_encode($request->orderItems),
            'shipping_address' => json_encode($request->shippingAddress),
            'payment_method' => $request->paymentMethod,
            'items_price' => $request->itemsPrice,
            'tax_price' => $request->taxPrice,
            'shipping_price' => $request->shippingPrice,
            'total_price' => $request->totalPrice,
            'is_paid' => false,
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

    // Update Order to Paid
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
        $orders = Order::where('user_id', Auth::id())->get();
        return response()->json($orders);
    }

    // Get All Orders (Admin)
    public function getOrders()
    {
        $orders = Order::with('user:id,first_name,last_name')->get();
        return response()->json($orders);
    }
}
