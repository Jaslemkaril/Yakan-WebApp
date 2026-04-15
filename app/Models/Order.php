<?php

/**
 * Order Model
 * 
 * Represents an order placed from mobile app
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    /**
     * ALL fields that exist in the database.
     * Base table: order_ref, user_id, customer_name, customer_email, customer_phone,
     *   subtotal, shipping_fee, discount, total, delivery_type, shipping_address,
     *   shipping_city, shipping_province, payment_method, payment_status, payment_reference,
     *   payment_verified_at, status, notes, admin_notes, source, confirmed_at, shipped_at,
     *   delivered_at, cancelled_at
     * Added by migrations: coupon_id, coupon_code, discount_amount, delivery_address,
     *   delivery_latitude, delivery_longitude, bank_receipt, gcash_receipt, payment_proof_path,
     *   tracking_number, total_amount, user_address_id, tracking_status, tracking_history,
     *   customer_notes
     */
    protected $fillable = [
        // Base table columns
        'order_ref',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
        'delivery_type',
        'shipping_address',
        'shipping_city',
        'shipping_province',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_verified_at',
        'status',
        'notes',
        'admin_notes',
        'source',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        // Added by later migrations
        'coupon_id',
        'coupon_code',
        'discount_amount',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'bank_receipt',
        'gcash_receipt',
        'payment_proof_path',
        'tracking_number',
        'total_amount',
        'user_address_id',
        'tracking_status',
        'tracking_history',
        'customer_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tracking_history' => 'array',
        'payment_verified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who placed the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(\App\Models\UserAddress::class, 'user_address_id');
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for items() relationship
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Refund requests submitted by the customer for this order.
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(OrderRefundRequest::class);
    }

    /**
     * Determine if this order can accept a new refund request.
     */
    public function canRequestRefund(): bool
    {
        if (strtolower((string) $this->status) !== 'completed') {
            return false;
        }

        if (!$this->isRefundWithinWarranty()) {
            return false;
        }

        if (!Schema::hasTable('order_refund_requests')) {
            return true;
        }

        $activeStatuses = ['requested', 'under_review', 'approved', 'processed'];

        return !$this->refundRequests()->whereIn('status', $activeStatuses)->exists();
    }

    /**
     * Warranty window for refund requests in days.
     */
    public function getRefundWarrantyDays(): int
    {
        return max(1, (int) config('orders.refund_warranty_days', 7));
    }

    /**
     * Start timestamp used for refund warranty counting.
     */
    public function getRefundWarrantyStartAt(): ?Carbon
    {
        return $this->confirmed_at ?? $this->delivered_at ?? $this->updated_at;
    }

    /**
     * Deadline until when refunds are allowed.
     */
    public function getRefundWarrantyDeadline(): ?Carbon
    {
        $startAt = $this->getRefundWarrantyStartAt();
        if (!$startAt) {
            return null;
        }

        return $startAt->copy()->addDays($this->getRefundWarrantyDays());
    }

    /**
     * Whether order is still inside refund warranty window.
     */
    public function isRefundWithinWarranty(): bool
    {
        if (strtolower((string) $this->status) !== 'completed') {
            return false;
        }

        $deadline = $this->getRefundWarrantyDeadline();
        if (!$deadline) {
            return false;
        }

        return now()->lte($deadline);
    }

    /**
     * Generate unique order reference
     */
    public static function generateOrderRef(): string
    {
        $prefix = now()->format('ymd'); // YYMMDD
        $random = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $ref    = $prefix . $random;

        // Ensure uniqueness
        while (static::where('order_ref', $ref)->exists()) {
            $random = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $ref    = $prefix . $random;
        }

        return $ref;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending_confirmation' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope to get pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending_confirmation');
    }

    /**
     * Scope to get recent orders
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Scope by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Append a tracking event to the order's history
     */
    public function appendTrackingEvent(string $status, string $date = null): void
    {
        $history = $this->tracking_history ?? [];
        
        // Handle case where tracking_history is a string (not properly cast)
        if (is_string($history)) {
            $history = json_decode($history, true) ?? [];
        }
        
        $history[] = [
            'status' => $status,
            'date' => $date ?? now()->format('Y-m-d h:i A'),
        ];
        $this->tracking_history = $history;
    }

    /**
     * Get the display customer name (from user relationship or customer_name field)
     */
    public function getDisplayCustomerName(): string
    {
        return $this->user ? $this->user->name : ($this->customer_name ?? 'N/A');
    }

    /**
     * Get the display customer email (from user relationship or customer_email field)
     */
    public function getDisplayCustomerEmail(): string
    {
        return $this->user ? $this->user->email : ($this->customer_email ?? 'N/A');
    }
}
