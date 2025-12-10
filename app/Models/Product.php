<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'YKN-' . strtoupper(substr(uniqid(), -8));
            }
        });
    }

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'image',
        'status',
        'sku',
        'available_sizes',
        'available_colors',
        'all_images',
    ];

    protected $casts = [
        'price' => 'float',
        'available_sizes' => 'array',
        'available_colors' => 'array',
        'all_images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->approved();
    }

    public function allReviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->inventory ? $this->inventory->quantity : $this->stock;
    }

    public function isInStock(): bool
    {
        return $this->getAvailableStockAttribute() > 0;
    }

    public function isLowStock(): bool
    {
        return $this->inventory ? $this->inventory->isLowStock() : false;
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->isInStock()) {
            return 'Out of Stock';
        } elseif ($this->isLowStock()) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    public function getStockStatusColorAttribute(): string
    {
        if (!$this->isInStock()) {
            return 'text-red-600 bg-red-50';
        } elseif ($this->isLowStock()) {
            return 'text-yellow-600 bg-yellow-50';
        } else {
            return 'text-green-600 bg-green-50';
        }
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getRatingBreakdownAttribute(): array
    {
        $breakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $breakdown[$i] = [
                'count' => $this->reviews()->where('rating', $i)->count(),
                'percentage' => $this->review_count > 0 
                    ? ($this->reviews()->where('rating', $i)->count() / $this->review_count) * 100 
                    : 0
            ];
        }
        return $breakdown;
    }

    public function canBeReviewedBy(User $user): bool
    {
        return !$this->reviews()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return '';
        }

        $imagePath = $this->image;
        
        // If it's already a full URL, return as is
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        
        // Remove 'storage/' prefix if present
        if (strpos($imagePath, 'storage/') === 0) {
            $imagePath = str_replace('storage/', '', $imagePath);
        }
        
        // Add 'products/' prefix if not present
        if (strpos($imagePath, 'products/') !== 0 && strpos($imagePath, 'public/') !== 0) {
            $imagePath = 'products/' . $imagePath;
        }
        
        // Check if file exists in new uploads directory
        if (file_exists(public_path('uploads/' . $imagePath))) {
            return asset('uploads/' . $imagePath);
        }
        
        // Check if file exists in storage directory
        if (file_exists(public_path('storage/' . $imagePath))) {
            return asset('storage/' . $imagePath);
        }
        
        // Default to uploads for new paths
        return asset('uploads/' . $imagePath);
    }

    /**
     * Check if product has an image
     */
    public function hasImage(): bool
    {
        return !empty($this->image);
    }
}
