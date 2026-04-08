<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load('orderItems.product', 'user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation — ' . ($this->order->order_ref ?? 'ORD-' . str_pad($this->order->id, 5, '0', STR_PAD_LEFT)),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }
}
