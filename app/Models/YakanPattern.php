<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class YakanPattern extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'difficulty_level',
        'production_days',
        'pattern_data',
        'svg_path',
        'base_color',
        'color_variations',
        'base_price_multiplier',
        'pattern_price',
        'price_per_meter',
        'is_active',
        'popularity_score',
    ];

    protected $casts = [
        'pattern_data' => 'array',
        'color_variations' => 'array',
        'base_price_multiplier' => 'decimal:2',
        'pattern_price' => 'decimal:2',
        'price_per_meter' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function customOrders(): HasMany
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PatternMedia::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PatternTag::class, 'pattern_tag_pivot', 'yakan_pattern_id', 'pattern_tag_id');
    }

    public function getDifficultyColorAttribute(): string
    {
        $colors = [
            'simple' => 'green',
            'medium' => 'yellow',
            'complex' => 'red',
        ];

        return $colors[$this->difficulty_level] ?? 'gray';
    }

    public function getEstimatedDaysAttribute(): int
    {
        // Return production_days from database if set, otherwise fallback to difficulty-based estimation
        if ($this->production_days) {
            return $this->production_days;
        }

        $days = [
            'simple' => 7,
            'medium' => 14,
            'complex' => 21,
        ];

        return $days[$this->difficulty_level] ?? 14;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function incrementPopularity(): void
    {
        $this->increment('popularity_score');
    }

    /**
     * Check if pattern has SVG
     */
    public function hasSvg(): bool
    {
        return !empty($this->svg_path) || (!empty($this->pattern_data) && is_string($this->pattern_data) && str_contains($this->pattern_data, '<svg'));
    }

    /**
     * Get SVG content with customizable colors
     */
    public function getSvgContent(?array $colors = null): ?string
    {
        // If svg_path exists, read from file
        if (!empty($this->svg_path)) {
            $svgPath = public_path('uploads/patterns/svg/' . $this->svg_path);
            if (file_exists($svgPath)) {
                $svgContent = file_get_contents($svgPath);
            } else {
                return null;
            }
        }
        // If pattern_data contains SVG string
        elseif (!empty($this->pattern_data) && is_string($this->pattern_data) && str_contains($this->pattern_data, '<svg')) {
            $svgContent = $this->pattern_data;
        } else {
            return null;
        }

        // Apply color customization if provided
        if ($colors && !empty($svgContent)) {
            // Replace default colors with custom colors
            // Example: Replace fill colors
            foreach ($colors as $index => $color) {
                $svgContent = preg_replace(
                    '/fill\s*=\s*["\']#[0-9a-fA-F]{6}["\']/i',
                    'fill="' . $color . '"',
                    $svgContent,
                    1
                );
            }
        }

        return $svgContent;
    }

    /**
     * Get primary image URL (SVG or fallback to media)
     */
    public function getImageUrlAttribute(): string
    {
        // If has SVG, return data URI
        if ($this->hasSvg()) {
            $svgContent = $this->getSvgContent();
            if ($svgContent) {
                return 'data:image/svg+xml;base64,' . base64_encode($svgContent);
            }
        }

        // Fallback to first media image
        $firstMedia = $this->media()->first();
        if ($firstMedia) {
            return $firstMedia->url;
        }

        return '';
    }
}
