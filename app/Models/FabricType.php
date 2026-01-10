<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FabricType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'base_price_per_meter',
        'material_composition',
        'weight_gsm',
        'texture',
        'typical_uses',
        'care_instructions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'base_price_per_meter' => 'decimal:2',
        'weight_gsm' => 'integer',
        'typical_uses' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active fabric types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get pattern compatibilities for this fabric type
     */
    public function patternCompatibilities(): HasMany
    {
        return $this->hasMany(PatternFabricCompatibility::class);
    }

    /**
     * Get compatible patterns
     */
    public function compatiblePatterns()
    {
        return $this->belongsToMany(YakanPattern::class, 'pattern_fabric_compatibility')
            ->withPivot(['difficulty_level', 'price_multiplier', 'estimated_production_days', 'notes', 'is_recommended'])
            ->wherePivot('is_active', true)
            ->orderBy('pivot_is_recommended', 'desc')
            ->orderBy('name');
    }

    /**
     * Scope for sorting
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->base_price_per_meter, 2);
    }

    /**
     * Get weight with unit
     */
    public function getWeightWithUnitAttribute(): string
    {
        return $this->weight_gsm ? $this->weight_gsm . ' GSM' : 'N/A';
    }

    /**
     * Check if fabric is suitable for specific use
     */
    public function isSuitableFor(string $use): bool
    {
        return in_array($use, $this->typical_uses ?? []);
    }

    /**
     * Get recommended patterns for this fabric
     */
    public function getRecommendedPatterns()
    {
        return $this->compatiblePatterns()
            ->wherePivot('is_recommended', true)
            ->get();
    }

    /**
     * Calculate base price for quantity
     */
    public function calculateBasePrice(float $meters): float
    {
        return $this->base_price_per_meter * $meters;
    }

    /**
     * Get production estimate for pattern
     */
    public function getPatternProductionEstimate(int $patternId): ?int
    {
        $compatibility = $this->patternCompatibilities()
            ->where('yakan_pattern_id', $patternId)
            ->where('is_active', true)
            ->first();

        return $compatibility?->estimated_production_days;
    }

    /**
     * Get pattern price multiplier
     */
    public function getPatternPriceMultiplier(int $patternId): float
    {
        $compatibility = $this->patternCompatibilities()
            ->where('yakan_pattern_id', $patternId)
            ->where('is_active', true)
            ->first();

        return $compatibility?->price_multiplier ?? 1.00;
    }
}
