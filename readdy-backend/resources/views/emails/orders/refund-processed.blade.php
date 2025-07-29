<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Processed</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .refund-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .order-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
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
        <h1>✅ Refund Processed</h1>
        <p>Hi {{ $user->name }}, your refund has been completed!</p>
    </div>

    <div class="content">
        <div class="refund-section">
            <h2>💰 Refund Completed</h2>
            <p>Your refund for order #{{ $order->order_number }} has been successfully processed and credited to your account.</p>
            
            <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p><strong>Refund Amount:</strong> ${{ number_format($refundInfo['refund_amount'] ?? $order->total_amount, 2) }}</p>
                <p><strong>Refund Method:</strong> {{ $refundInfo['refund_method'] ?? 'Original payment method' }}</p>
                <p><strong>Transaction ID:</strong> {{ $refundInfo['transaction_id'] ?? 'N/A' }}</p>
                <p><strong>Processed Date:</strong> {{ now()->format('M j, Y \a\t g:i A') }}</p>
            </div>

            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4>🎉 What's Next?</h4>
                <ul>
                    <li>The refunded amount should appear in your account within 1-2 business days</li>
                    <li>You can check your bank statement or payment provider for the credit</li>
                    <li>If you have any questions, please contact our support team</li>
                </ul>
            </div>
        </div>

        <div class="order-details">
            <h2>Order Information</h2>
            <p><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p><strong>Original Order Date:</strong> {{ $order->created_at->format('M j, Y \a\t g:i A') }}</p>
            <p><strong>Cancellation Date:</strong> {{ $order->updated_at->format('M j, Y \a\t g:i A') }}</p>
            
            <h3>Refunded Items:</h3>
            @foreach($order->items as $item)
                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;">
                    <h4>{{ $item->book->title }}</h4>
                    <p><strong>Author:</strong> {{ $item->book->author }}</p>
                    <p><strong>Refunded Amount:</strong> ${{ number_format($item->price, 2) }}</p>
                </div>
            @endforeach
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $orderUrl }}" class="btn">View Order Details</a>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>Need Help?</h3>
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
        <p>Thank you for your patience. We hope to serve you again soon!</p>
        <p>© {{ date('Y') }} Readdy. All rights reserved.</p>
    </div>
</body>
</html> 