<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone_number',
        'street',
        'barangay',
        'city',
        'province',
        'postal_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns this address
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [
            $this->street,
            $this->barangay,
            $this->city,
            $this->province,
            $this->postal_code,
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Set this address as default for the user
     */
    public function setAsDefault(): void
    {
        // Remove default from all other addresses
        UserAddress::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Scope to get default address
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get addresses for a user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
