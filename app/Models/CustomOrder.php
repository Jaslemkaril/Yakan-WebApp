<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'specifications',
        'design_upload',
        'quantity',
        'status',
        'payment_status',
        'patterns',
        // Fabric Customization Fields
        'fabric_type',
        'fabric_weight_gsm',
        'fabric_quantity_meters',
        'intended_use',
        'fabric_specifications',
        'special_requirements',
        // Contact Information
        'phone',
        'email',
        'delivery_address',
        'delivery_type',
        // Product Details (legacy - making nullable)
        'product_type',
        'dimensions',
        'preferred_colors',
        // Color Customization
        'primary_color',
        'secondary_color',
        'accent_color',
        // Budget & Timeline
        'budget_range',
        'expected_date',
        'urgency',
        // Additional Details
        'additional_notes',
        'estimated_price',
        'final_price',
        // Admin fields
        'admin_notes',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'price_quoted_at',
        'user_notified_at',
        'production_completed_at',
        'out_for_delivery_at',
        'delivered_at',
        // Payment fields
        'payment_method',
        'transaction_id',
        'payment_receipt',
        'payment_notes',
        'transfer_date',
        // Visual Design Fields
        'design_method',
        'design_metadata',
        'design_version',
        'canvas_width',
        'canvas_height',
        'pattern_positions',
        'color_palette',
        'artisan_notes',
        'design_approved_at',
        'design_approved_by',
        'design_modifications',
        'last_design_update',
        'design_completion_time',
        'pattern_count',
        'complexity_score',
        // NOTE: Following fields were in fillable but don't exist as DB columns:
        // 'order_name', 'category', 'size', 'priority', 'description', 'special_instructions'
        // 'preview_image', 'customization_settings', 'complexity'
        // Data is stored in 'specifications', 'design_upload', and 'design_metadata' instead
    ];

    protected $casts = [
        'patterns' => 'array',
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'fabric_quantity_meters' => 'decimal:2',
        'fabric_weight_gsm' => 'integer',
        'expected_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'price_quoted_at' => 'datetime',
        'user_notified_at' => 'datetime',
        'production_completed_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'transfer_date' => 'date',
        'design_metadata' => 'array',
        'pattern_positions' => 'array',
        'color_palette' => 'array',
        'design_modifications' => 'array',
        'design_approved_at' => 'datetime',
        'last_design_update' => 'datetime',
        'canvas_width' => 'decimal:2',
        'canvas_height' => 'decimal:2',
        'design_completion_time' => 'integer',
        'pattern_count' => 'integer',
    ];

    protected $with = ['product', 'user'];

    protected static function booted()
    {
        static::addGlobalScope('withRelations', function ($query) {
            // Only load essential relationships by default
            $query->with(['user:id,name,email', 'product:id,name,price,image']);
        });
    }

    /**
     * Relation to User with optimized select
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

    /**
     * Relation to Product with optimized select
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->select(['id', 'name', 'price', 'image']);
    }

    /**
     * Get the reviews for this custom order
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'custom_order_id');
    }

    /**
     * Get fabric type (if fabric_type_id exists in future)
     */
    public function fabricType()
    {
        // For now, return null since we're storing fabric_type as string
        // This can be updated later to use a relationship
        return null;
    }

    /**
     * Check if this is a fabric custom order
     */
    public function isFabricOrder(): bool
    {
        return !empty($this->fabric_type) && !empty($this->fabric_quantity_meters);
    }

    /**
     * Get fabric total area (meters × width assumption)
     */
    public function getFabricTotalAreaAttribute(): float
    {
        // Assuming standard fabric width of 1.5 meters
        $standardWidth = 1.5;
        return $this->fabric_quantity_meters * $standardWidth;
    }

    /**
     * Get formatted fabric quantity
     */
    public function getFormattedFabricQuantityAttribute(): string
    {
        return number_format($this->fabric_quantity_meters, 2) . ' meters';
    }

    /**
     * Get intended use label
     */
    public function getIntendedUseLabelAttribute(): string
    {
        return match($this->intended_use) {
            'clothing' => 'Clothing & Garments',
            'home_decor' => 'Home Decor',
            'crafts' => 'Crafts & Accessories',
            default => ucfirst($this->intended_use ?? 'Other')
        };
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for orders awaiting user decision
     */
    public function scopeAwaitingDecision($query)
    {
        return $query->where('status', 'price_quoted')
                    ->whereNotNull('user_notified_at');
    }

    /**
     * Scope for orders by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent orders
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get orders with statistics efficiently
     */
    public static function getWithStatistics(int $userId)
    {
        return static::byUser($userId)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_orders,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END) as total_spent
            ')
            ->first();
    }

    /**
     * Business Logic Methods for Custom Order Workflow
     */

    /**
     * Check if order is waiting for admin review
     */
    public function isPendingReview(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if admin has quoted price and waiting for user response
     */
    public function isPriceQuoted(): bool
    {
        return $this->status === 'price_quoted' && $this->final_price !== null;
    }

    /**
     * Check if user can accept/reject the quoted price
     */
    public function isAwaitingUserDecision(): bool
    {
        return $this->status === 'price_quoted' && 
               $this->final_price !== null && 
               $this->user_notified_at !== null;
    }

    /**
     * Check if order is ready for payment
     */
    public function isReadyForPayment(): bool
    {
        return $this->status === 'price_quoted' && 
               $this->final_price !== null && 
               $this->user_notified_at !== null;
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Admin quotes price for the custom order
     */
    public function quotePrice(float $price, string $adminNotes = null): bool
    {
        if (!$this->isPendingReview()) {
            return false;
        }

        $this->final_price = $price;
        $this->admin_notes = $adminNotes;
        $this->price_quoted_at = now();
        $this->status = 'price_quoted';
        
        return $this->save();
    }

    /**
     * Admin rejects the custom order
     */
    public function reject(string $reason): bool
    {
        if (!$this->isPendingReview()) {
            return false;
        }

        $this->rejection_reason = $reason;
        $this->rejected_at = now();
        $this->status = 'rejected';
        
        return $this->save();
    }

    /**
     * Notify user about the quoted price
     */
    public function notifyUser(): bool
    {
        if (!$this->isPriceQuoted()) {
            return false;
        }

        $this->user_notified_at = now();
        return $this->save();
    }

    /**
     * User accepts the quoted price and proceeds to payment
     */
    public function acceptPrice(): bool
    {
        if (!$this->isAwaitingUserDecision()) {
            return false;
        }

        $this->status = 'approved';
        $this->approved_at = now();
        
        return $this->save();
    }

    /**
     * User rejects the quoted price (cancels order)
     */
    public function rejectPrice(): bool
    {
        if (!$this->isAwaitingUserDecision()) {
            return false;
        }

        $this->status = 'cancelled';
        $this->rejection_reason = 'User rejected the quoted price';
        
        return $this->save();
    }

    /**
     * User cancels the order (only if not yet paid)
     */
    public function cancel(): bool
    {
        if (in_array($this->status, ['completed', 'processing'])) {
            return false;
        }

        $this->status = 'cancelled';
        $this->rejection_reason = $this->rejection_reason ?? 'User cancelled the order';
        
        return $this->save();
    }

    /**
     * Mark order as completed after payment and production
     */
    public function markAsCompleted(): bool
    {
        if (!in_array($this->status, ['processing', 'in_production'])) {
            return false;
        }

        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Get status description for user display
     */
    public function getStatusDescription(): string
    {
        return match($this->status) {
            'pending' => 'Waiting for admin review',
            'price_quoted' => 'Price quoted - awaiting your decision',
            'approved' => 'Order approved - ready for payment',
            'in_production' => 'Order in production',
            'processing' => 'Order in production',
            'completed' => 'Order completed',
            'rejected' => 'Order rejected',
            'cancelled' => 'Order cancelled',
            default => 'Unknown status'
        };
    }

    /**
     * Get available actions for current user
     */
    public function getAvailableActions(): array
    {
        if ($this->isPendingReview()) {
            return ['cancel'];
        }

        if ($this->isAwaitingUserDecision()) {
            return ['accept_price', 'reject_price'];
        }

        if ($this->status === 'approved' && $this->payment_status === 'pending') {
            return ['pay'];
        }

        return [];
    }
}
