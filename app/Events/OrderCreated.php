<?php

/**
 * OrderCreated Event
 * 
 * Fired when a new order is created from mobile app
 * Broadcasts to admin dashboard for real-time notification
 */

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event' => 'order.created',
            'order' => [
                'id' => $this->order->id,
                'order_ref' => $this->order->order_ref,
                'customer_name' => $this->order->customer_name,
                'customer_phone' => $this->order->customer_phone,
                'total' => $this->order->total,
                'status' => $this->order->status,
                'created_at' => $this->order->created_at->toIso8601String(),
            ],
            'message' => "New order #{$this->order->order_ref} from {$this->order->customer_name}",
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
