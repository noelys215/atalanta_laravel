<!DOCTYPE html>
<html>
<head>
    <title>Order Paid</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f6f6f6;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 2rem auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .content {
            text-align: left;
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #888888;
        }
        .order-item {
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .order-item img {
            max-width: 250px;
            height: auto;
            display: block;
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
        }
        .order-item strong {
            font-size: 16px;
        }
        .footer-message {
            text-align: center;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Hello!</h1>
        <p>Thank you for your payment.</p>
    </div>
    <div class="content">
        <p><strong>Order ID:</strong> {{ $order->id }}</p>
        <p><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</p>
        <p><strong>Shipping Address:</strong></p>
        <p>{{ nl2br(e($order->shipping_address)) }}</p>
        <hr/>
        <p><strong>Order Items:</strong></p>
        @foreach ($orderItems as $item)
            <div class="order-item">
                <p><strong>Name:</strong> {{ $item['name'] }}</p>
                <p><strong>Quantity:</strong> {{ $item['quantity'] }}</p>
                <p><strong>Price:</strong> ${{ number_format($item['price'], 2) }}</p>
                <p><strong>Size:</strong> {{ $item['selectedSize'] }}</p>
                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
            </div>
        @endforeach
        <p class="footer-message">Thank you for shopping with us!</p>
    </div>
    <div class="footer">
        <p>Regards, Atalanta A.C.</p>
    </div>
</div>
</body>
</html>
