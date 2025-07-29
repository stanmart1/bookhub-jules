<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $user;
    public $downloadLinks;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->user = $order->user;
        $this->downloadLinks = $this->generateDownloadLinks();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation - Order #{$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'downloadLinks' => $this->downloadLinks,
                'receiptUrl' => $this->generateReceiptUrl(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Generate download links for digital books
     */
    private function generateDownloadLinks(): array
    {
        $links = [];
        
        foreach ($this->order->items as $item) {
            if ($item->book && $item->book->bookFile) {
                $links[] = [
                    'book_title' => $item->book->title,
                    'author' => $item->book->author,
                    'download_url' => route('api.v1.delivery.download', [
                        'order_id' => $this->order->id,
                        'book_id' => $item->book_id,
                        'token' => app(\App\Services\DeliveryService::class)->generateDownloadToken($this->order->id, $item->book_id)
                    ]),
                    'expires_at' => now()->addDays(7)->format('M j, Y \a\t g:i A'),
                ];
            }
        }
        
        return $links;
    }

    /**
     * Generate receipt URL
     */
    private function generateReceiptUrl(): string
    {
        return route('api.v1.orders.receipt', [
            'order' => $this->order->id,
            'token' => app(\App\Services\ReceiptService::class)->generateReceiptToken($this->order->id)
        ]);
    }
} 