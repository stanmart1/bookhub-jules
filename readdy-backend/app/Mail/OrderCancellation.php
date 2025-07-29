<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $user;
    public $refundInfo;
    public $cancellationReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $cancellationReason = null)
    {
        $this->order = $order;
        $this->user = $order->user;
        $this->cancellationReason = $cancellationReason;
        $this->refundInfo = $this->getRefundInfo();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Cancelled - Order #{$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.cancellation',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'refundInfo' => $this->refundInfo,
                'cancellationReason' => $this->cancellationReason,
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
     * Get refund information
     */
    private function getRefundInfo(): array
    {
        $refundInfo = [
            'refund_amount' => $this->order->total_amount,
            'refund_method' => 'Original payment method',
            'processing_time' => '3-5 business days',
            'refund_status' => 'Processing',
        ];

        // If there's a payment record, get more specific refund info
        if ($this->order->payment) {
            $refundInfo['refund_method'] = $this->order->payment->gateway_name;
            $refundInfo['transaction_id'] = $this->order->payment->transaction_id;
        }

        return $refundInfo;
    }
} 