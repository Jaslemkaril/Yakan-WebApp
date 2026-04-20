<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderKpiService
{
    public const PENDING_CONFIRMATION_STATUSES = ['pending_confirmation', 'pending'];
    public const PROCESSING_SHIPPING_STATUSES = ['confirmed', 'processing', 'shipped'];
    public const DONE_ORDER_STATUSES = ['delivered', 'completed'];
    public const PAID_PAYMENT_STATUSES = ['paid', 'completed', 'verified'];

    public function getSharedOrderStatusCounts(): array
    {
        return [
            'pending_confirmation' => Order::whereIn('status', self::PENDING_CONFIRMATION_STATUSES)->count(),
            'processing_shipping' => Order::whereIn('status', self::PROCESSING_SHIPPING_STATUSES)->count(),
            'done_orders' => Order::whereIn('status', self::DONE_ORDER_STATUSES)->count(),
        ];
    }

    public function getRefundedTodayCount(): int
    {
        $refundedDateColumn = Schema::hasColumn('orders', 'refunded_at') ? 'refunded_at' : 'updated_at';

        return Order::where(function ($query) {
            $query->where('status', 'refunded')
                ->orWhereRaw('LOWER(COALESCE(payment_status, "")) = ?', ['refunded']);
        })->whereDate($refundedDateColumn, today())->count();
    }

    public function getStaffDashboardStats(): array
    {
        $sharedCounts = $this->getSharedOrderStatusCounts();

        return [
            'pending_confirmation' => $sharedCounts['pending_confirmation'],
            'processing_shipping' => $sharedCounts['processing_shipping'],
            'done_orders' => $sharedCounts['done_orders'],
            'refunded_today' => $this->getRefundedTodayCount(),
        ];
    }

    public function getAdminOrdersManagementStats(): array
    {
        $sharedCounts = $this->getSharedOrderStatusCounts();
        $paidRevenueExpression = $this->resolvePaidRevenueExpression();

        return [
            'total_orders' => Order::count(),
            'pending_orders' => $sharedCounts['pending_confirmation'],
            'processing_orders' => $sharedCounts['processing_shipping'],
            'shipped_orders' => Order::whereRaw('LOWER(status) = ?', ['shipped'])->count(),
            'delivered_orders' => $sharedCounts['done_orders'],
            'total_revenue' => Order::whereIn('payment_status', self::PAID_PAYMENT_STATUSES)
                ->sum(DB::raw($paidRevenueExpression)),
            'pending_revenue' => Order::where('payment_status', 'pending')
                ->sum(DB::raw($paidRevenueExpression)),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                ->whereIn('payment_status', self::PAID_PAYMENT_STATUSES)
                ->sum(DB::raw($paidRevenueExpression)),
        ];
    }

    private function resolvePaidRevenueExpression(): string
    {
        $supportsDownpayment = Schema::hasColumn('orders', 'payment_option')
            && Schema::hasColumn('orders', 'downpayment_amount')
            && Schema::hasColumn('orders', 'remaining_balance')
            && Schema::hasColumn('orders', 'total_amount');

        if (!$supportsDownpayment) {
            return 'total_amount';
        }

        return "CASE WHEN payment_option = 'downpayment' AND COALESCE(remaining_balance, 0) > 0 THEN downpayment_amount ELSE total_amount END";
    }
}
