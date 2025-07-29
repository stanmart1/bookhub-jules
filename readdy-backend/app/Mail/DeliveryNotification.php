<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $user;
    public $deliveredItems;
    public $downloadLinks;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, array $deliveredItems = [])
    {
        $this->order = $order;
        $this->user = $order->user;
        $this->deliveredItems = $deliveredItems;
        $this->downloadLinks = $this->generateDownloadLinks($deliveredItems);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $itemCount = count($this->deliveredItems);
        $subject = $itemCount === 1 
            ? "Your book is ready for download!" 
            : "Your {$itemCount} books are ready for download!";
            
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.delivery.notification',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'deliveredItems' => $this->deliveredItems,
                'downloadLinks' => $this->downloadLinks,
                'orderUrl' => route('api.v1.orders.show', $this->order->id),
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
     * Generate download links for delivered items
     */
    private function generateDownloadLinks(array $deliveredItems): array
    {
        $links = [];
        
        foreach ($deliveredItems as $item) {
            if ($item['book'] && $item['book']->bookFile) {
                $links[] = [
                    'book_title' => $item['book']->title,
                    'author' => $item['book']->author,
                    'download_url' => route('api.v1.delivery.download', [
                        'order_id' => $this->order->id,
                        'book_id' => $item['book_id'],
                        'token' => app(\App\Services\DeliveryService::class)->generateDownloadToken($this->order->id, $item['book_id'])
                    ]),
                    'expires_at' => now()->addDays(7)->format('M j, Y \a\t g:i A'),
                    'file_size' => $this->formatFileSize($item['book']->bookFile->file_size),
                    'file_format' => strtoupper($item['book']->bookFile->file_format),
                ];
            }
        }
        
        return $links;
    }

    /**
     * Format file size for display
     */
    private function formatFileSize(?int $bytes): string
    {
        if (!$bytes) return 'Unknown';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 