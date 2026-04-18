<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    public const LOW_STOCK_MIN_ALERT_QUANTITY = 10;

    protected $table = 'inventory';
    
    protected $fillable = [
        'product_id',
        'quantity',
        'min_stock_level',
        'max_stock_level',
        'cost_price',
        'selling_price',
        'supplier',
        'location',
        'notes',
        'low_stock_alert',
        'last_restocked_at',
        'total_sold',
        'total_revenue',
        'last_sale_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'low_stock_alert' => 'boolean',
        'last_restocked_at' => 'datetime',
        'total_sold' => 'integer',
        'total_revenue' => 'decimal:2',
        'last_sale_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity >= self::LOW_STOCK_MIN_ALERT_QUANTITY
            && $this->quantity <= $this->min_stock_level;
    }

    public function isOverstock(): bool
    {
        return $this->quantity >= $this->max_stock_level;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isLowStock()) {
            return 'Low Stock';
        } elseif ($this->isOverstock()) {
            return 'Overstock';
        } else {
            return 'Normal';
        }
    }

    public function getStockStatusColorAttribute(): string
    {
        if ($this->isLowStock()) {
            return 'text-red-600 bg-red-50';
        } elseif ($this->isOverstock()) {
            return 'text-yellow-600 bg-yellow-50';
        } else {
            return 'text-green-600 bg-green-50';
        }
    }

    public function updateStock(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->low_stock_alert = $this->isLowStock();
        $this->save();
    }

    public function restock(int $quantity): void
    {
        $this->quantity += $quantity;
        $this->last_restocked_at = now();
        $this->low_stock_alert = $this->isLowStock();
        $this->save();
    }

    public function decrementStock(int $quantity): bool
    {
        if ($this->quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->total_sold += $quantity;
            
            // Calculate revenue based on selling price or product price
            $price = $this->selling_price ?? $this->product->price ?? 0;
            $this->total_revenue += ($price * $quantity);
            $this->last_sale_at = now();
            
            $this->low_stock_alert = $this->isLowStock();
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Check if sufficient stock exists
     */
    public function hasSufficientStock(int $requiredQuantity): bool
    {
        return $this->quantity >= $requiredQuantity;
    }

    /**
     * Get available stock quantity
     */
    public function getAvailableStock(): int
    {
        return $this->quantity;
    }

    /**
     * Process order items and check inventory availability
     */
    public static function processOrderItems($orderItems): array
    {
        $results = [];
        $allSufficient = true;

        foreach ($orderItems as $item) {
            $inventory = self::where('product_id', $item->product_id)->first();

            if (!$inventory) {
                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'required' => $item->quantity,
                    'available' => 0,
                    'status' => 'no_inventory',
                    'message' => 'No inventory record found'
                ];
                $allSufficient = false;
                continue;
            }

            if ($inventory->hasSufficientStock($item->quantity)) {
                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'required' => $item->quantity,
                    'available' => $inventory->quantity,
                    'status' => 'sufficient',
                    'message' => 'Sufficient stock available'
                ];
            } else {
                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'required' => $item->quantity,
                    'available' => $inventory->quantity,
                    'status' => 'insufficient',
                    'message' => 'Insufficient stock'
                ];
                $allSufficient = false;
            }
        }

        return [
            'items' => $results,
            'all_sufficient' => $allSufficient
        ];
    }

    /**
     * Fulfill order and deduct inventory
     */
    public static function fulfillOrder($orderItems): array
    {
        $results = [];
        $allDeducted = true;

        foreach ($orderItems as $item) {
            $inventory = self::where('product_id', $item->product_id)->first();

            if (!$inventory) {
                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'status' => 'failed',
                    'message' => 'No inventory record found'
                ];
                $allDeducted = false;
                continue;
            }

            if ($inventory->decrementStock($item->quantity)) {
                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'remaining_stock' => $inventory->quantity,
                    'status' => 'deducted',
                    'message' => 'Inventory deducted successfully'
                ];
            } else {
                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'available_stock' => $inventory->quantity,
                    'status' => 'failed',
                    'message' => 'Insufficient stock for deduction'
                ];
                $allDeducted = false;
            }
        }

        return [
            'items' => $results,
            'all_deducted' => $allDeducted
        ];
    }
}
