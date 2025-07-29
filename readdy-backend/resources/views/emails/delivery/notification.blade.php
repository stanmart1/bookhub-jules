<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Books Are Ready!</title>
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
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn-secondary {
            background: #6c757d;
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
        <h1>📚 Your Books Are Ready!</h1>
        <p>Hi {{ $user->name }}, your digital books are now available for download!</p>
    </div>

    <div class="content">
        <div class="download-section">
            <h2>Download Your Books</h2>
            <p>Your purchased books have been processed and are ready for immediate download. Click the links below to access your digital library.</p>
            
            @foreach($downloadLinks as $link)
                <div class="book-item">
                    <h3>{{ $link['book_title'] }}</h3>
                    <p><strong>Author:</strong> {{ $link['author'] }}</p>
                    <p><strong>Format:</strong> {{ $link['file_format'] }}</p>
                    <p><strong>File Size:</strong> {{ $link['file_size'] }}</p>
                    <p><strong>Download Link:</strong> <a href="{{ $link['download_url'] }}" class="btn">Download Now</a></p>
                    <p><small><strong>Link expires:</strong> {{ $link['expires_at'] }}</small></p>
                </div>
            @endforeach
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4>📋 Important Information:</h4>
                <ul>
                    <li>Download links are valid for 7 days</li>
                    <li>You can download each book up to 3 times</li>
                    <li>Make sure you have a stable internet connection</li>
                    <li>Keep your download links secure and private</li>
                </ul>
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $orderUrl }}" class="btn btn-secondary">View Order Details</a>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>Having Trouble?</h3>
            <p>If you encounter any issues with downloading your books:</p>
            <ul>
                <li>Check your internet connection</li>
                <li>Try using a different browser</li>
                <li>Contact our support team for assistance</li>
            </ul>
            <p><strong>Support:</strong> support@readdy.com | +1 (555) 123-4567</p>
        </div>
    </div>

    <div class="footer">
        <p>Happy reading from the Readdy team!</p>
        <p>© {{ date('Y') }} Readdy. All rights reserved.</p>
    </div>
</body>
</html> 