<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left: 4px solid #667eea;
        }
        .download-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .book-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn-secondary {
            background: #6c757d;
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
        <h1>🎉 Order Confirmed!</h1>
        <p>Thank you for your purchase, {{ $user->name }}!</p>
    </div>

    <div class="content">
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('M j, Y \a\t g:i A') }}</p>
            <p><strong>Status:</strong> <span style="color: #28a745;">{{ ucfirst($order->status) }}</span></p>
            
            <h3>Items Purchased:</h3>
            @foreach($order->items as $item)
                <div class="book-item">
                    <h4>{{ $item->book->title }}</h4>
                    <p><strong>Author:</strong> {{ $item->book->author }}</p>
                    <p><strong>Price:</strong> ${{ number_format($item->price, 2) }}</p>
                    @if($item->book->bookFile)
                        <p><strong>Format:</strong> {{ strtoupper($item->book->bookFile->file_format) }}</p>
                        <p><strong>Size:</strong> {{ $this->formatFileSize($item->book->bookFile->file_size) }}</p>
                    @endif
                </div>
            @endforeach

            <div class="total">
                <p><strong>Subtotal:</strong> ${{ number_format($order->subtotal, 2) }}</p>
                @if($order->discount_amount > 0)
                    <p><strong>Discount:</strong> -${{ number_format($order->discount_amount, 2) }}</p>
                @endif
                @if($order->tax_amount > 0)
                    <p><strong>Tax:</strong> ${{ number_format($order->tax_amount, 2) }}</p>
                @endif
                <p><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>
            </div>
        </div>

        @if(count($downloadLinks) > 0)
            <div class="download-section">
                <h2>📚 Download Your Books</h2>
                <p>Your digital books are ready for download! Click the links below to access your purchases.</p>
                
                @foreach($downloadLinks as $link)
                    <div class="book-item">
                        <h4>{{ $link['book_title'] }}</h4>
                        <p><strong>Author:</strong> {{ $link['author'] }}</p>
                        <p><strong>Download Link:</strong> <a href="{{ $link['download_url'] }}" class="btn">Download Now</a></p>
                        <p><small><strong>Link expires:</strong> {{ $link['expires_at'] }}</small></p>
                    </div>
                @endforeach
                
                <p><strong>Important:</strong> Download links are valid for 7 days. Please download your books within this timeframe.</p>
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $receiptUrl }}" class="btn btn-secondary">View Receipt</a>
            <a href="{{ route('api.v1.orders.show', $order->id) }}" class="btn">View Order Details</a>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>Need Help?</h3>
            <p>If you have any questions about your order or need assistance with downloads, please don't hesitate to contact our support team:</p>
            <ul>
                <li>Email: support@readdy.com</li>
                <li>Phone: +1 (555) 123-4567</li>
                <li>Live Chat: Available on our website</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for choosing Readdy for your reading needs!</p>
        <p>© {{ date('Y') }} Readdy. All rights reserved.</p>
    </div>
</body>
</html> 