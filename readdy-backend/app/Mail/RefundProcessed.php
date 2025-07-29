<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundProcessed extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $user;
    public $refundInfo;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, array $refundInfo = [])
    {
        $this->order = $order;
        $this->user = $order->user;
        $this->refundInfo = $refundInfo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Refund Processed - Order #{$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.refund-processed',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'refundInfo' => $this->refundInfo,
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
} 