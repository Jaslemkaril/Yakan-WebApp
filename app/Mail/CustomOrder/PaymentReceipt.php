<?php

namespace App\Mail\CustomOrder;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CustomOrder $customOrder,
        public int $itemCount = 1,
        public float $totalAmount = 0.0
    ) {
        $this->customOrder->loadMissing('user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Custom Order Payment Receipt - ' . $this->customOrder->display_ref,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-orders.payment-receipt',
            with: [
                'order' => $this->customOrder,
                'user' => $this->customOrder->user,
                'itemCount' => max(1, $this->itemCount),
                'totalAmount' => max(0, $this->totalAmount),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
