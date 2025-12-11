<?php

/**
 * OrderStatusChanged Event
 * 
 * Fired when admin updates order status
 * Broadcasts to mobile app for real-time status updates
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

class OrderStatusChanged implements ShouldBroadcast
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
            new Channel('orders.' . $this->order->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event' => 'order.status_changed',
            'order_id' => $this->order->id,
            'order_ref' => $this->order->order_ref,
            'status' => $this->order->status,
            'status_label' => $this->order->status_label,
            'message' => "Order #{$this->order->order_ref} status updated to {$this->order->status_label}",
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
