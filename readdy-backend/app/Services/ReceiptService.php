<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReceipt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceiptService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Generate receipt for an order.
     */
    public function generateReceipt(Order $order): OrderReceipt
    {
        try {
            // Check if receipt already exists
            $existingReceipt = OrderReceipt::where('order_id', $order->id)->first();
            if ($existingReceipt && $existingReceipt->is_generated) {
                return $existingReceipt;
            }

            // Create receipt data
            $receiptData = $this->buildReceiptData($order);

            // Create or update receipt record
            $receipt = OrderReceipt::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'receipt_number' => $this->generateReceiptNumber(),
                    'receipt_data' => $receiptData,
                    'file_type' => 'pdf',
                    'is_generated' => false,
                ]
            );

            // Generate PDF file
            $filePath = $this->generatePdfReceipt($receipt, $receiptData);

            // Update receipt with file path
            $receipt->update([
                'file_path' => $filePath,
                'is_generated' => true,
                'generated_at' => now(),
            ]);

            return $receipt;

        } catch (\Exception $e) {
            Log::error('Error generating receipt: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build receipt data for an order.
     */
    private function buildReceiptData(Order $order): array
    {
        $order->load(['user', 'items.book', 'payment']);

        return [
            'receipt_number' => $this->generateReceiptNumber(),
            'order_number' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i:s'),
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'title' => $item->title,
                    'author' => $item->author,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->price * $item->quantity,
                ];
            })->toArray(),
            'payment' => [
                'method' => $order->payment->payment_method ?? 'Unknown',
                'reference' => $order->payment->payment_reference ?? 'N/A',
                'gateway' => $order->payment->gateway_name ?? 'N/A',
            ],
            'totals' => [
                'subtotal' => $order->total_amount,
                'tax' => 0, // No tax for digital books
                'total' => $order->total_amount,
                'currency' => $order->currency,
            ],
            'company' => [
                'name' => 'Readdy',
                'address' => 'Digital Book Store',
                'email' => 'support@readdy.com',
                'website' => 'https://readdy.com',
            ],
        ];
    }

    /**
     * Generate a unique receipt number.
     */
    private function generateReceiptNumber(): string
    {
        do {
            $receiptNumber = 'RCP-' . strtoupper(Str::random(8)) . '-' . time();
        } while (OrderReceipt::where('receipt_number', $receiptNumber)->exists());

        return $receiptNumber;
    }

    /**
     * Generate PDF receipt file.
     */
    private function generatePdfReceipt(OrderReceipt $receipt, array $data): string
    {
        // For now, we'll create a simple HTML receipt and store it
        // In production, you'd use a PDF library like DomPDF or Snappy
        $html = $this->generateReceiptHtml($data);
        
        $filename = 'receipts/' . $receipt->receipt_number . '.html';
        Storage::disk('public')->put($filename, $html);

        return $filename;
    }

    /**
     * Generate HTML receipt template.
     */
    private function generateReceiptHtml(array $data): string
    {
        $itemsHtml = '';
        foreach ($data['items'] as $item) {
            $itemsHtml .= "
                <tr>
                    <td>{$item['title']} by {$item['author']}</td>
                    <td>{$item['quantity']}</td>
                    <td>{$data['totals']['currency']} " . number_format($item['price'], 2) . "</td>
                    <td>{$data['totals']['currency']} " . number_format($item['subtotal'], 2) . "</td>
                </tr>
            ";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt - {$data['receipt_number']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
                .receipt-info { margin-bottom: 20px; }
                .customer-info { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .totals { text-align: right; font-weight: bold; }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$data['company']['name']}</h1>
                <p>{$data['company']['address']}</p>
                <p>Email: {$data['company']['email']} | Website: {$data['company']['website']}</p>
            </div>

            <div class='receipt-info'>
                <h2>Receipt</h2>
                <p><strong>Receipt Number:</strong> {$data['receipt_number']}</p>
                <p><strong>Order Number:</strong> {$data['order_number']}</p>
                <p><strong>Date:</strong> {$data['order_date']}</p>
            </div>

            <div class='customer-info'>
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {$data['customer']['name']}</p>
                <p><strong>Email:</strong> {$data['customer']['email']}</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemsHtml}
                </tbody>
            </table>

            <div class='totals'>
                <p><strong>Subtotal:</strong> {$data['totals']['currency']} " . number_format($data['totals']['subtotal'], 2) . "</p>
                <p><strong>Tax:</strong> {$data['totals']['currency']} " . number_format($data['totals']['tax'], 2) . "</p>
                <p><strong>Total:</strong> {$data['totals']['currency']} " . number_format($data['totals']['total'], 2) . "</p>
            </div>

            <div class='payment-info'>
                <h3>Payment Information</h3>
                <p><strong>Method:</strong> {$data['payment']['method']}</p>
                <p><strong>Reference:</strong> {$data['payment']['reference']}</p>
                <p><strong>Gateway:</strong> {$data['payment']['gateway']}</p>
            </div>

            <div class='footer'>
                <p>Thank you for your purchase!</p>
                <p>This is a digital receipt for your records.</p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get receipt for an order.
     */
    public function getReceipt(Order $order): ?OrderReceipt
    {
        $receipt = OrderReceipt::where('order_id', $order->id)->first();

        if (!$receipt) {
            // Generate receipt if it doesn't exist
            return $this->generateReceipt($order);
        }

        return $receipt;
    }

    /**
     * Download receipt file.
     */
    public function downloadReceipt(OrderReceipt $receipt): ?string
    {
        if (!$receipt->is_generated || !$receipt->file_path) {
            return null;
        }

        $filePath = storage_path('app/public/' . $receipt->file_path);
        
        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }
}
