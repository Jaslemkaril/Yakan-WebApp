<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('orderItems.product', 'user');
    }

    public function envelope(): Envelope
    {
        $reference = $this->order->order_ref ?? ('ORD-' . str_pad((string) $this->order->id, 5, '0', STR_PAD_LEFT));

        return new Envelope(
            subject: 'Payment Receipt - ' . $reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-payment-receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
