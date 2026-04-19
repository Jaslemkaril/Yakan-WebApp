<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'YKN-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });

        // Auto-repair: if image is null but all_images has entries, set image from first all_images entry
        static::retrieved(function ($product) {
            if (empty($product->image) && !empty($product->all_images)) {
                $allImages = $product->all_images;
                $firstPath = collect($allImages)
                    ->map(fn($entry) => $product->extractImagePathFromGalleryEntry($entry))
                    ->first(fn($path) => !empty($path));

                if (!empty($firstPath)) {
                    $product->image = $firstPath;
                    // Persist the fix silently so it doesn't need repair again
                    static::withoutEvents(function () use ($product) {
                        $product->updateQuietly(['image' => $product->image]);
                    });
                }
            }
        });
    }

    protected $fillable = [
        'name',
        'description',
        'price',
        'discount_type',
        'discount_value',
        'discount_starts_at',
        'discount_ends_at',
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
        'discount_value' => 'float',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
        'available_sizes' => 'array',
        'available_colors' => 'array',
    ];

    public function getAllImagesAttribute($value): array
    {
        return $this->normalizeImageGalleryValue($value);
    }

    public function setAllImagesAttribute($value): void
    {
        $normalized = $this->normalizeImageGalleryValue($value);
        $this->attributes['all_images'] = empty($normalized)
            ? null
            : json_encode($normalized, JSON_UNESCAPED_SLASHES);
    }

    public function hasActiveProductDiscount(?Carbon $at = null): bool
    {
        $at = $at ?? now();
        $type = strtolower((string) ($this->discount_type ?? ''));
        $value = (float) ($this->discount_value ?? 0);

        if (!in_array($type, ['percent', 'fixed'], true) || $value <= 0) {
            return false;
        }

        if ($this->discount_starts_at && $at->lt($this->discount_starts_at)) {
            return false;
        }

        if ($this->discount_ends_at && $at->gt($this->discount_ends_at)) {
            return false;
        }

        return true;
    }

    public function getDiscountedPrice(float $basePrice, ?Carbon $at = null): float
    {
        $basePrice = max(0, $basePrice);

        if (!$this->hasActiveProductDiscount($at)) {
            return round($basePrice, 2);
        }

        $type = strtolower((string) ($this->discount_type ?? ''));
        $value = (float) ($this->discount_value ?? 0);

        $discountAmount = $type === 'percent'
            ? $basePrice * (min(100, max(0, $value)) / 100)
            : min($basePrice, $value);

        return round(max(0, $basePrice - $discountAmount), 2);
    }

    public function getDiscountAmount(float $basePrice, ?Carbon $at = null): float
    {
        $discounted = $this->getDiscountedPrice($basePrice, $at);
        return round(max(0, $basePrice - $discounted), 2);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bundleItems(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class, 'bundle_product_id');
    }

    public function bundledInItems(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
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
        if ($this->getIsBundleAttribute()) {
            return $this->calculateBundleAvailableStock();
        }

        return $this->inventory ? $this->inventory->quantity : $this->stock;
    }

    private function calculateBundleAvailableStock(): int
    {
        if (!Schema::hasTable('product_bundle_items')) {
            return (int) ($this->inventory?->quantity ?? $this->stock ?? 0);
        }

        $bundleItems = $this->relationLoaded('bundleItems')
            ? $this->bundleItems
            : $this->bundleItems()->with(['componentProduct.inventory', 'componentProduct.variants'])->get();

        if ($bundleItems->isEmpty()) {
            return (int) ($this->inventory?->quantity ?? $this->stock ?? 0);
        }

        $maxBundleUnits = null;

        foreach ($bundleItems as $bundleItem) {
            $component = $bundleItem->componentProduct;
            if (!$component) {
                return 0;
            }

            $componentStock = (int) ($component->inventory?->quantity ?? $component->stock ?? 0);
            $activeVariants = $component->relationLoaded('variants')
                ? $component->variants->where('is_active', true)
                : $component->variants()->where('is_active', true)->get();

            if ($activeVariants->isNotEmpty()) {
                $componentStock = (int) $activeVariants->sum('stock');
            }

            $requiredPerBundle = max(1, (int) $bundleItem->quantity);
            $possibleUnits = intdiv(max(0, $componentStock), $requiredPerBundle);
            $maxBundleUnits = is_null($maxBundleUnits)
                ? $possibleUnits
                : min($maxBundleUnits, $possibleUnits);
        }

        return max(0, (int) ($maxBundleUnits ?? 0));
    }

    public function isInStock(): bool
    {
        return $this->getAvailableStockAttribute() > 0;
    }

    public function isLowStock(): bool
    {
        return $this->inventory ? $this->inventory->isLowStock() : false;
    }

    public function hasActiveVariants(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->where('is_active', true)->isNotEmpty();
        }

        return $this->variants()->where('is_active', true)->exists();
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

    public function getIsBundleAttribute(): bool
    {
        if (!Schema::hasTable('product_bundle_items')) {
            return false;
        }

        if (array_key_exists('bundle_items_count', $this->attributes)) {
            return (int) $this->attributes['bundle_items_count'] > 0;
        }

        if ($this->relationLoaded('bundleItems')) {
            return $this->bundleItems->isNotEmpty();
        }

        return $this->bundleItems()->exists();
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
        $candidates = $this->getImagePathCandidates();
        if (empty($candidates)) {
            return '';
        }

        $resolved = $this->resolveBestImageUrl();
        if ($resolved !== null) {
            return $resolved;
        }

        return rtrim((string) config('app.url'), '/') . '/images/no-image.svg';
    }

    /**
     * Get the product image source for use in views
     * Handles both Cloudinary URLs and local file paths
     * Returns placeholder if image is missing
     */
    public function getImageSrcAttribute(): string
    {
        return $this->resolveBestImageUrl() ?? asset('images/no-image.svg');
    }

    /**
     * Check if product has an actual accessible image
     */
    public function hasImage(): bool
    {
        return $this->resolveBestImageUrl() !== null;
    }

    /**
     * Check if this product needs its image re-uploaded to Cloudinary
     */
    public function getNeedsImageUploadAttribute(): bool
    {
        return !$this->hasImage();
    }

    /**
     * Collect possible image path candidates from primary image and gallery images.
     */
    private function getImagePathCandidates(): array
    {
        $candidates = [];

        if (!empty($this->image)) {
            $candidates[] = (string) $this->image;
        }

        $galleryImages = $this->all_images;

        if (is_array($galleryImages)) {
            foreach ($galleryImages as $entry) {
                $path = $this->extractImagePathFromGalleryEntry($entry);
                if (!empty($path)) {
                    $candidates[] = $path;
                }
            }
        }

        return $this->normalizeImageCandidates($candidates);
    }

    /**
     * Collect candidate image paths from active variants.
     */
    private function getVariantImagePathCandidates(): array
    {
        if (!Schema::hasTable('product_variants')) {
            return [];
        }

        $variants = $this->relationLoaded('variants')
            ? $this->variants->values()
            : $this->variants()
                ->get(['id', 'product_id', 'image', 'is_active']);

        if ($variants->isEmpty()) {
            return [];
        }

        $activeCandidates = $variants
            ->where('is_active', true)
            ->pluck('image')
            ->filter(fn($value) => !empty($value))
            ->values()
            ->all();

        $candidates = !empty($activeCandidates)
            ? $activeCandidates
            : $variants
                ->pluck('image')
                ->filter(fn($value) => !empty($value))
                ->values()
                ->all();

        return $this->normalizeImageCandidates($candidates);
    }

    /**
     * Collect candidate image paths from bundle component products.
     */
    private function getBundleComponentImagePathCandidates(): array
    {
        if (!Schema::hasTable('product_bundle_items')) {
            return [];
        }

        $bundleItems = $this->relationLoaded('bundleItems')
            ? $this->bundleItems
            : $this->bundleItems()->with(['componentProduct:id,image,all_images'])->get();

        $candidates = [];

        foreach ($bundleItems as $bundleItem) {
            $component = $bundleItem->componentProduct;
            if (!$component) {
                continue;
            }

            if (!empty($component->image)) {
                $candidates[] = (string) $component->image;
            }

            $componentGallery = $component->all_images;

            if (is_array($componentGallery)) {
                foreach ($componentGallery as $entry) {
                    $path = $this->extractImagePathFromGalleryEntry($entry);
                    if (!empty($path)) {
                        $candidates[] = $path;
                    }
                }
            }

            // If component has variant-only images, include those as fallback too.
            $candidates = array_merge($candidates, $component->getVariantImagePathCandidates());
        }

        return $this->normalizeImageCandidates($candidates);
    }

    /**
     * Normalize and deduplicate image candidates.
     */
    private function normalizeImageCandidates(array $candidates): array
    {
        $normalized = array_map(function ($value) {
            return trim(str_replace('\\', '/', (string) $value));
        }, $candidates);

        $filtered = array_values(array_filter($normalized, fn($value) => $value !== ''));

        return array_values(array_unique($filtered));
    }

    /**
     * Resolve any supported image path format into a publicly accessible URL.
     */
    private function resolveImagePathToUrl(string $rawPath): ?string
    {
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }

        if (str_starts_with($rawPath, '//')) {
            return 'https:' . $rawPath;
        }

        $path = ltrim($rawPath, '/');
        if ($path === '') {
            return null;
        }

        $candidates = [$path];

        if (str_starts_with($path, 'public/')) {
            $candidates[] = 'storage/' . substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            $candidates[] = 'uploads/' . substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'products/')) {
            $candidates[] = 'uploads/' . $path;
            $candidates[] = 'storage/' . $path;
        }

        if (str_starts_with($path, 'variants/')) {
            $candidates[] = 'uploads/' . $path;
            $candidates[] = 'storage/' . $path;
        }

        if (str_starts_with($path, 'product-variants/')) {
            $candidates[] = 'uploads/' . $path;
            $candidates[] = 'storage/' . $path;
        }

        if (!str_contains($path, '/')) {
            $candidates[] = 'uploads/products/' . $path;
            $candidates[] = 'uploads/variants/' . $path;
            $candidates[] = 'uploads/product-variants/' . $path;
            $candidates[] = 'storage/products/' . $path;
            $candidates[] = 'storage/variants/' . $path;
            $candidates[] = 'storage/product-variants/' . $path;
            $candidates[] = 'products/' . $path;
            $candidates[] = 'variants/' . $path;
            $candidates[] = 'product-variants/' . $path;
        }

        foreach (array_values(array_unique($candidates)) as $candidate) {
            $publicCandidate = ltrim($candidate, '/');
            if (file_exists(public_path($publicCandidate))) {
                return asset($publicCandidate);
            }
        }

        // Some deployments can serve files that are not directly visible to PHP via file_exists().
        foreach (array_values(array_unique($candidates)) as $candidate) {
            $publicCandidate = ltrim($candidate, '/');
            if (
                str_starts_with($publicCandidate, 'uploads/')
                || str_starts_with($publicCandidate, 'storage/')
                || str_starts_with($publicCandidate, 'products/')
                || str_starts_with($publicCandidate, 'variants/')
                || str_starts_with($publicCandidate, 'product-variants/')
            ) {
                return asset($publicCandidate);
            }
        }

        return null;
    }

    private function normalizeImageGalleryValue($value): array
    {
        $decoded = $value;

        for ($i = 0; $i < 3 && is_string($decoded); $i++) {
            $trimmed = trim($decoded);
            if ($trimmed === '' || strtolower($trimmed) === 'null') {
                return [];
            }

            $jsonDecoded = json_decode($trimmed, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $decoded = $jsonDecoded;
        }

        if (!is_array($decoded)) {
            return [];
        }

        $normalized = [];

        foreach ($decoded as $entry) {
            $path = $this->extractImagePathFromGalleryEntry($entry);
            if (empty($path)) {
                continue;
            }

            $normalized[] = [
                'path' => $path,
                'color' => is_array($entry) ? ($entry['color'] ?? null) : null,
                'sort_order' => is_array($entry) && isset($entry['sort_order'])
                    ? (int) $entry['sort_order']
                    : count($normalized),
            ];
        }

        return $normalized;
    }

    private function extractImagePathFromGalleryEntry($entry): ?string
    {
        if (is_string($entry)) {
            $path = trim($entry);
            return $path !== '' ? $path : null;
        }

        if (!is_array($entry)) {
            return null;
        }

        foreach (['path', 'image_path', 'url', 'image_url', 'src'] as $key) {
            if (!empty($entry[$key]) && is_string($entry[$key])) {
                return trim($entry[$key]);
            }
        }

        return null;
    }

    /**
     * Resolve the best available image URL from known image candidates.
     */
    private function resolveBestImageUrl(): ?string
    {
        foreach ($this->getImagePathCandidates() as $candidate) {
            $resolved = $this->resolveImagePathToUrl($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        foreach ($this->getVariantImagePathCandidates() as $candidate) {
            $resolved = $this->resolveImagePathToUrl($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        foreach ($this->getBundleComponentImagePathCandidates() as $candidate) {
            $resolved = $this->resolveImagePathToUrl($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }
}
