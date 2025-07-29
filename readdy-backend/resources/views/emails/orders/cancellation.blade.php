<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Cancelled</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .order-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .refund-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>❌ Order Cancelled</h1>
        <p>Hi {{ $user->name }}, your order has been cancelled.</p>
    </div>

    <div class="content">
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('M j, Y \a\t g:i A') }}</p>
            <p><strong>Cancellation Date:</strong> {{ now()->format('M j, Y \a\t g:i A') }}</p>
            <p><strong>Status:</strong> <span style="color: #dc3545;">Cancelled</span></p>
            
            @if($cancellationReason)
                <p><strong>Reason:</strong> {{ $cancellationReason }}</p>
            @endif

            <h3>Cancelled Items:</h3>
            @foreach($order->items as $item)
                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;">
                    <h4>{{ $item->book->title }}</h4>
                    <p><strong>Author:</strong> {{ $item->book->author }}</p>
                    <p><strong>Price:</strong> ${{ number_format($item->price, 2) }}</p>
                </div>
            @endforeach

            <div class="total">
                <p><strong>Total Refund Amount:</strong> ${{ number_format($refundInfo['refund_amount'], 2) }}</p>
            </div>
        </div>

        <div class="refund-section">
            <h2>💰 Refund Information</h2>
            <p>Your refund has been processed and will be credited back to your original payment method.</p>
            
            <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p><strong>Refund Amount:</strong> ${{ number_format($refundInfo['refund_amount'], 2) }}</p>
                <p><strong>Refund Method:</strong> {{ $refundInfo['refund_method'] }}</p>
                <p><strong>Processing Time:</strong> {{ $refundInfo['processing_time'] }}</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">{{ $refundInfo['refund_status'] }}</span></p>
                @if(isset($refundInfo['transaction_id']))
                    <p><strong>Transaction ID:</strong> {{ $refundInfo['transaction_id'] }}</p>
                @endif
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4>📋 Important Notes:</h4>
                <ul>
                    <li>Refunds typically appear in your account within 3-5 business days</li>
                    <li>The exact timing depends on your bank or payment provider</li>
                    <li>You will receive a confirmation email once the refund is completed</li>
                    <li>If you don't see the refund after 5 business days, please contact us</li>
                </ul>
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $orderUrl }}" class="btn">View Order Details</a>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>Questions About Your Refund?</h3>
            <p>If you have any questions about your refund or need assistance:</p>
            <ul>
                <li>Email: support@readdy.com</li>
                <li>Phone: +1 (555) 123-4567</li>
                <li>Live Chat: Available on our website</li>
            </ul>
            <p>Please include your order number (#{{ $order->order_number }}) when contacting us.</p>
        </div>
    </div>

    <div class="footer">
        <p>We apologize for any inconvenience. Thank you for your understanding.</p>
        <p>© {{ date('Y') }} Readdy. All rights reserved.</p>
    </div>
</body>
</html> 