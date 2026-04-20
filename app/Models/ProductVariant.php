<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'image',
        'price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getImageSrcAttribute(): ?string
    {
        $rawPath = trim((string) ($this->image ?? ''));
        if ($rawPath === '') {
            return null;
        }

        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }

        if (str_starts_with($rawPath, '//')) {
            return 'https:' . $rawPath;
        }

        $path = ltrim(str_replace('\\', '/', $rawPath), '/');
        $candidates = [$path];

        if (!str_contains($path, '/')) {
            $candidates[] = 'uploads/variants/' . $path;
            $candidates[] = 'uploads/product-variants/' . $path;
            $candidates[] = 'uploads/products/' . $path;
            $candidates[] = 'storage/variants/' . $path;
            $candidates[] = 'storage/product-variants/' . $path;
            $candidates[] = 'storage/products/' . $path;
            $candidates[] = 'product-variants/' . $path;
        }

        if (str_starts_with($path, 'public/')) {
            $candidates[] = 'storage/' . substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            $candidates[] = 'uploads/' . substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'variants/')) {
            $candidates[] = 'uploads/' . $path;
            $candidates[] = 'storage/' . $path;
        }

        if (str_starts_with($path, 'product-variants/')) {
            $candidates[] = 'uploads/' . $path;
            $candidates[] = 'storage/' . $path;
            $candidates[] = 'variants/' . substr($path, strlen('product-variants/'));
        }

        if (str_starts_with($path, 'products/')) {
            $candidates[] = 'uploads/' . $path;
            $candidates[] = 'storage/' . $path;
        }

        foreach (array_values(array_unique($candidates)) as $candidate) {
            $publicCandidate = ltrim($candidate, '/');
            if (file_exists(public_path($publicCandidate))) {
                return asset($publicCandidate);
            }
        }

        foreach (array_values(array_unique($candidates)) as $candidate) {
            $publicCandidate = ltrim($candidate, '/');
            $normalizedCandidate = $publicCandidate;

            // Public assets are served from /uploads or /storage, not bare /variants.
            if (str_starts_with($normalizedCandidate, 'variants/')) {
                $normalizedCandidate = 'uploads/' . $normalizedCandidate;
            } elseif (str_starts_with($normalizedCandidate, 'product-variants/')) {
                $normalizedCandidate = 'uploads/' . $normalizedCandidate;
            } elseif (str_starts_with($normalizedCandidate, 'products/')) {
                $normalizedCandidate = 'uploads/' . $normalizedCandidate;
            }

            if (
                str_starts_with($normalizedCandidate, 'uploads/')
                || str_starts_with($normalizedCandidate, 'storage/')
            ) {
                return asset($normalizedCandidate);
            }
        }

        return null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->size,
            $this->color,
        ]);

        return !empty($parts) ? implode(' / ', $parts) : 'Default Variant';
    }
}
