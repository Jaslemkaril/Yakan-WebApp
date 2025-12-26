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
    protected $fillable = [
        'order_ref',
        'tracking_number',
        'user_id',
        'user_address_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
        'total_amount',
        'discount_amount',
        'coupon_id',
        'coupon_code',
        'delivery_type',
        'shipping_address',
        'delivery_address',
        'shipping_city',
        'shipping_province',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_proof_path',
        'payment_verified_at',
        'status',
        'tracking_status',
        'tracking_history',
        'bank_receipt',
        'notes',
        'customer_notes',
        'admin_notes',
        'source',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'courier_name',
        'courier_contact',
        'courier_tracking_url',
        'estimated_delivery_date',
        'tracking_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tracking_history' => 'json',
        'payment_verified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'estimated_delivery_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->order_ref) {
                $model->order_ref = static::generateOrderRef();
            }
        });
    }

    /**
     * Get the user who placed the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the delivery address
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the order items (alias)
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the reviews for this order
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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
     * Append tracking event to tracking history
     */
    public function appendTrackingEvent(string $message): void
    {
        $history = $this->tracking_history ?? [];
        
        // If it's a JSON string, decode it
        if (is_string($history)) {
            $history = json_decode($history, true) ?? [];
        }
        
        // Ensure it's an array
        if (!is_array($history)) {
            $history = [];
        }
        
        // Add new event
        $history[] = [
            'status' => $message,
            'date' => now()->format('Y-m-d h:i A')
        ];
        
        // Update tracking history
        $this->tracking_history = json_encode($history);
    }
}
