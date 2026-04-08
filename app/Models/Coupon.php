<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CouponRedemption;
use App\Models\User;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'min_spend',
        'usage_limit',
        'usage_limit_per_user',
        'times_redeemed',
        'starts_at',
        'ends_at',
        'active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_spend' => 'decimal:2',
    ];

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function isActive(): bool
    {
        if (!$this->active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        if ($this->usage_limit && $this->times_redeemed >= $this->usage_limit) return false;
        return true;
    }

    public function canBeUsedBy(?User $user): bool
    {
        if (!$this->isActive()) return false;
        if (!$user || !$this->usage_limit_per_user) return true;
        $count = $this->redemptions()->where('user_id', $user->id)->count();
        return $count < $this->usage_limit_per_user;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < (float)$this->min_spend) return 0.0;
        $discount = $this->type === 'percent'
            ? ($subtotal * ((float)$this->value) / 100.0)
            : (float)$this->value;
        if (!is_null($this->max_discount)) {
            $discount = min($discount, (float)$this->max_discount);
        }
        $discount = max(0.0, min($discount, $subtotal));
        return round($discount, 2);
    }

    /**
     * Calculate discount applied only to the shipping fee.
     * min_spend is checked separately against the order subtotal.
     */
    public function calculateShippingDiscount(float $shippingFee): float
    {
        if ($shippingFee <= 0) return 0.0;
        $discount = $this->type === 'percent'
            ? ($shippingFee * ((float)$this->value) / 100.0)
            : (float)$this->value;
        // Cannot discount more than the actual shipping fee
        $discount = max(0.0, min($discount, $shippingFee));
        return round($discount, 2);
    }
}
