<?php

namespace App\Services\Notification;

use App\Models\Order;
use App\Models\CustomOrder;
use App\Models\Notification;
use App\Models\AdminNotification;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class OrderStatusNotificationService
{
    /**
     * Send notifications when order status changes
     */
    public function notifyOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        try {
            // Notify user
            $this->notifyUserOrderStatusChange($order, $oldStatus, $newStatus);
            
            // Notify admin
            $this->notifyAdminOrderStatusChange($order, $oldStatus, $newStatus);
            
            Log::info('Order status change notifications sent', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status change notifications', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications when custom order status changes
     */
    public function notifyCustomOrderStatusChange(CustomOrder $order, string $oldStatus, string $newStatus): void
    {
        try {
            // Notify user
            $this->notifyUserCustomOrderStatusChange($order, $oldStatus, $newStatus);
            
            // Notify admin
            $this->notifyAdminCustomOrderStatusChange($order, $oldStatus, $newStatus);
            
            Log::info('Custom order status change notifications sent', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send custom order status change notifications', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify user about order status change
     */
    protected function notifyUserOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        if (!$order->user_id) {
            return;
        }

        $statusMessages = [
            'pending' => 'Your order is pending confirmation.',
            'processing' => 'Your order is now being processed!',
            'shipped' => 'Your order has been shipped!',
            'delivered' => 'Your order has been delivered!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $title = "Order #{$order->id} - " . ucfirst($newStatus);
        $message = $statusMessages[$newStatus] ?? "Your order status has been updated to {$newStatus}.";

        Notification::createNotification(
            $order->user_id,
            'order',
            $title,
            $message,
            route('orders.show', $order->id),
            [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'payment_status' => $order->payment_status,
            ]
        );
    }

    /**
     * Notify user about custom order status change
     */
    protected function notifyUserCustomOrderStatusChange(CustomOrder $order, string $oldStatus, string $newStatus): void
    {
        if (!$order->user_id) {
            return;
        }

        $statusMessages = [
            'pending' => 'Your custom order is pending review.',
            'processing' => 'Your custom order is now being processed!',
            'price_quoted' => 'A price quote has been provided for your custom order!',
            'completed' => 'Your custom order has been completed!',
            'cancelled' => 'Your custom order has been cancelled.',
            'approved' => 'Your custom order has been approved!',
            'rejected' => 'Your custom order has been rejected.',
        ];

        $title = "Custom Order #{$order->id} - " . ucfirst(str_replace('_', ' ', $newStatus));
        $message = $statusMessages[$newStatus] ?? "Your custom order status has been updated to {$newStatus}.";

        Notification::createNotification(
            $order->user_id,
            'custom_order',
            $title,
            $message,
            route('custom_orders.show', $order->id),
            [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'final_price' => $order->final_price,
            ]
        );
    }

    /**
     * Notify admin about order status change
     */
    protected function notifyAdminOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        $userName = $order->user->name ?? 'Guest';
        $title = "Order #{$order->id} Status Changed";
        $message = "{$userName}'s order status changed from {$oldStatus} to {$newStatus}";

        AdminNotification::notifyAllAdmins(
            'order',
            $title,
            $message,
            route('admin.orders.show', $order->id),
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'user_name' => $userName,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'total' => $order->total,
            ]
        );
    }

    /**
     * Notify admin about custom order status change
     */
    protected function notifyAdminCustomOrderStatusChange(CustomOrder $order, string $oldStatus, string $newStatus): void
    {
        $userName = $order->user->name ?? 'Guest';
        $title = "Custom Order #{$order->id} Status Changed";
        $message = "{$userName}'s custom order status changed from {$oldStatus} to {$newStatus}";

        AdminNotification::notifyAllAdmins(
            'custom_order',
            $title,
            $message,
            route('admin.custom_orders.show', $order->id),
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'user_name' => $userName,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'final_price' => $order->final_price,
            ]
        );
    }

    /**
     * Notify admin about new order
     */
    public function notifyAdminNewOrder(Order $order): void
    {
        try {
            $userName = $order->user->name ?? 'Guest';
            $title = "New Order #" . $order->id;
            $message = "{$userName} placed a new order worth ₱" . number_format($order->total, 2);

            AdminNotification::notifyAllAdmins(
                'order',
                $title,
                $message,
                route('admin.orders.show', $order->id),
                [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'user_name' => $userName,
                    'total' => $order->total,
                ]
            );

            Log::info('Admin notified of new order', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admin of new order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify admin about new custom order
     */
    public function notifyAdminNewCustomOrder(CustomOrder $order): void
    {
        try {
            $userName = $order->user->name ?? 'Guest';
            $title = "New Custom Order #" . $order->id;
            $message = "{$userName} submitted a new custom order request";

            AdminNotification::notifyAllAdmins(
                'custom_order',
                $title,
                $message,
                route('admin.custom_orders.show', $order->id),
                [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'user_name' => $userName,
                    'estimated_price' => $order->estimated_price,
                ]
            );

            Log::info('Admin notified of new custom order', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admin of new custom order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
