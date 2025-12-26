<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'custom_order_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'verified_purchase',
        'is_approved',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order this review is for
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the custom order this review is for
     */
    public function customOrder(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class);
    }

    /**
     * Get the order item this review is for
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the admin who approved this review
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get approved reviews only
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get pending reviews
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope to get reviews for a product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId)->approved();
    }

    /**
     * Scope to get reviews for an order
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope to get reviews for a custom order
     */
    public function scopeForCustomOrder($query, $customOrderId)
    {
        return $query->where('custom_order_id', $customOrderId);
    }

    /**
     * Scope to get reviews by a user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get average rating for a product
     */
    public static function getAverageRating($productId)
    {
        return static::forProduct($productId)->avg('rating') ?? 0;
    }

    /**
     * Get rating distribution for a product
     */
    public static function getRatingDistribution($productId)
    {
        return static::forProduct($productId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();
    }

    /**
     * Get total review count for a product
     */
    public static function getReviewCount($productId)
    {
        return static::forProduct($productId)->count();
    }

    /**
     * Mark review as helpful
     */
    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Mark review as unhelpful
     */
    public function markAsUnhelpful(): void
    {
        $this->increment('unhelpful_count');
    }

    /**
     * Approve review
     */
    public function approve($adminId = null): bool
    {
        $this->is_approved = true;
        $this->approved_by = $adminId;
        $this->approved_at = now();
        return $this->save();
    }

    /**
     * Reject review
     */
    public function reject($reason, $adminId = null): bool
    {
        $this->is_approved = false;
        $this->rejection_reason = $reason;
        $this->approved_by = $adminId;
        return $this->save();
    }

    /**
     * Get star rating display
     */
    public function getStarDisplay(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Check if review is verified purchase
     */
    public function isVerifiedPurchase(): bool
    {
        return $this->verified_purchase;
    }
}
