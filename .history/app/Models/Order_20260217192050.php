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
     * Generate unique order reference
     */
    public static function generateOrderRef(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return sprintf('ORD-%s-%03d', $date, $count);
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
