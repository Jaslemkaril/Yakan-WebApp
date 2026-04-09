<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Inventory;
use App\Services\TransactionalMailService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ((string) $order->payment_status === 'paid') {
            $this->sendPaymentReceiptEmail($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status') && (string) $order->payment_status === 'paid') {
            $this->sendPaymentReceiptEmail($order);
        }

        // Check if status changed to 'completed' and inventory hasn't been deducted yet
        if ($order->wasChanged('status') && $order->status === 'completed') {
            $this->processOrderCompletion($order);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    /**
     * Process order completion and deduct inventory
     */
    private function processOrderCompletion(Order $order): void
    {
        try {
            // Load order items with products
            $orderItems = $order->items()->with('product')->get();

            if ($orderItems->isEmpty()) {
                Log::info('No order items found for order completion', ['order_id' => $order->id]);
                return;
            }

            // Process inventory deduction
            $result = Inventory::fulfillOrder($orderItems);

            // Log the results
            Log::info('Order fulfillment processed', [
                'order_id' => $order->id,
                'all_deducted' => $result['all_deducted'],
                'items_processed' => count($result['items']),
                'results' => $result['items']
            ]);

            // If any items failed to deduct, log warning
            if (!$result['all_deducted']) {
                $failedItems = array_filter($result['items'], fn($item) => $item['status'] === 'failed');
                Log::warning('Some items could not be deducted from inventory', [
                    'order_id' => $order->id,
                    'failed_items' => $failedItems
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error processing order fulfillment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function sendPaymentReceiptEmail(Order $order): void
    {
        try {
            $order->loadMissing('user', 'orderItems.product');
            $email = trim((string) optional($order->user)->email);

            if ($email === '') {
                return;
            }

            TransactionalMailService::sendView(
                $email,
                'Payment Receipt - ' . ($order->order_ref ?? ('ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT))),
                'emails.order-payment-receipt',
                ['order' => $order]
            );
        } catch (\Throwable $exception) {
            Log::warning('Order payment receipt email failed from observer', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
